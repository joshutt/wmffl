<?php

namespace App\Tests\Repository;

use App\Entity\DraftPick;
use App\Repository\DraftPickRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DraftPickRepositoryTest extends TestCase
{
    // ---- getAsOfDate ----

    public function testGetAsOfDateReturnsWeek1ActivationDue(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchOne')
            ->with(
                $this->stringContains('WHERE Season = :season AND Week = 1'),
                ['season' => 2024]
            )
            ->willReturn('2024-09-05');

        $this->assertSame('2024-09-05', $this->makeRepo($conn)->getAsOfDate(2024));
    }

    public function testGetAsOfDateReturnsNullWhenNoWeek1Row(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn(false);

        $this->assertNull($this->makeRepo($conn)->getAsOfDate(2029));
    }

    // ---- getBoard: shape ----

    public function testGetBoardLeftJoinsPlayersSoUndraftedRowsSurvive(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->stringContains('LEFT JOIN players p ON p.playerid = dp.playerid'), $this->anything())
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2006, $this->noFilters(), null);
    }

    public function testGetBoardJoinsTeamnamesTwiceWithTeamFallbackOnBothSides(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('COALESCE(tn.name, t.name) AS team'),
                    $this->stringContains('LEFT JOIN teamnames tn ON tn.teamid = dp.teamid AND tn.season = dp.Season'),
                    $this->stringContains('LEFT JOIN team t ON t.teamid = dp.teamid'),
                    $this->stringContains('COALESCE(otn.name, ot.name) AS orgteamname'),
                    $this->stringContains('LEFT JOIN teamnames otn ON otn.teamid = dp.orgTeam AND otn.season = dp.Season'),
                    $this->stringContains('LEFT JOIN team ot ON ot.teamid = dp.orgTeam'),
                    $this->stringContains('dp.orgTeam AS orgteam')
                ),
                $this->anything()
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2027, $this->noFilters(), null);
    }

    public function testGetBoardOrdersNullPicksLastWithinRound(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->stringContains('ORDER BY dp.Round, dp.Pick IS NULL, dp.Pick'), $this->anything())
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2027, $this->noFilters(), null);
    }

    public function testGetBoardWithNoFiltersOnlyBindsSeason(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->anything(), ['season' => 2019])
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, $this->noFilters(), null);
    }

    // ---- getBoard: individual filters ----

    public function testGetBoardFiltersByRound(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('dp.Round = :round'),
                ['season' => 2019, 'round' => 3]
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, ['round' => 3, 'pick' => null, 'team' => null, 'pos' => null, 'nfl' => null], null);
    }

    public function testGetBoardFiltersByPick(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('dp.Pick = :pick'),
                ['season' => 2019, 'pick' => 5]
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, ['round' => null, 'pick' => 5, 'team' => null, 'pos' => null, 'nfl' => null], null);
    }

    public function testGetBoardFiltersByOwningTeamOnly(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('dp.teamid = :team'),
                    $this->logicalNot($this->stringContains('dp.orgTeam = :team'))
                ),
                ['season' => 2007, 'team' => '5']
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2007, ['round' => null, 'pick' => null, 'team' => '5', 'pos' => null, 'nfl' => null], null);
    }

    public function testGetBoardFiltersByPos(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('p.pos = :pos'),
                ['season' => 2019, 'pos' => 'OL']
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, ['round' => null, 'pick' => null, 'team' => null, 'pos' => 'OL', 'nfl' => null], null);
    }

    public function testGetBoardFiltersByNflViaHavingOnTheComputedColumn(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('HAVING nflteamid = :nfl'),
                ['season' => 2019, 'nfl' => 'SEA']
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, ['round' => null, 'pick' => null, 'team' => null, 'pos' => null, 'nfl' => 'SEA'], null);
    }

    public function testGetBoardCombinesMultipleFilters(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('dp.Round = :round'),
                    $this->stringContains('dp.teamid = :team')
                ),
                ['season' => 2019, 'round' => 2, 'team' => '5']
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, ['round' => 2, 'pick' => null, 'team' => '5', 'pos' => null, 'nfl' => null], null);
    }

    // ---- getBoard: as-of date resolution ----

    public function testGetBoardUsesBoundAsOfDateWhenGiven(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('nr.dateon <= :asOf AND (nr.dateoff IS NULL OR nr.dateoff >= :asOf)'),
                ['season' => 2019, 'asOf' => '2019-09-05']
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2019, $this->noFilters(), '2019-09-05');
    }

    public function testGetBoardDegradesToTodayWhenAsOfIsNull(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('nr.dateoff IS NULL'),
                    $this->logicalNot($this->stringContains(':asOf'))
                ),
                ['season' => 2029]
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getBoard(2029, $this->noFilters(), null);
    }

    public function testGetBoardReturnsRowsAsIs(): void
    {
        $rows = [['id' => 1, 'round' => 1, 'pick' => 1, 'teamid' => 5, 'team' => 'Gallic Warriors']];

        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn($rows);

        $this->assertSame($rows, $this->makeRepo($conn)->getBoard(2007, $this->noFilters(), null));
    }

    // ---- getFilterOptions ----

    public function testGetFilterOptionsDerivesRoundsAndPicksFromTheSeasonsActualData(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);
        $conn->method('fetchFirstColumn')->willReturnCallback(function (string $sql) {
            if (str_contains($sql, 'DISTINCT Round')) {
                return ['1', '2', '16'];
            }
            if (str_contains($sql, 'DISTINCT Pick')) {
                return ['1', '12'];
            }

            return [];
        });

        $options = $this->makeRepo($conn)->getFilterOptions(2013, null);

        $this->assertSame([1, 2, 16], $options['rounds']);
        $this->assertSame([1, 12], $options['picks']);
    }

    public function testGetFilterOptionsPicksExcludeNullPickRows(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->atLeastOnce())->method('fetchFirstColumn')
            ->willReturnCallback(function (string $sql, array $params = []) {
                if (str_contains($sql, 'DISTINCT Pick')) {
                    $this->assertStringContainsString('Pick IS NOT NULL', $sql);
                }

                return [];
            });
        $conn->method('fetchAllAssociative')->willReturn([]);

        $this->makeRepo($conn)->getFilterOptions(2027, null);
    }

    public function testGetFilterOptionsTeamsComeFromTeamnamesForTheSeason(): void
    {
        $rows = [['teamid' => '5', 'name' => 'Gallic Warriors']];

        $conn = $this->createMock(Connection::class);
        $conn->method('fetchFirstColumn')->willReturn([]);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->stringContains('FROM teamnames WHERE season = :season'), ['season' => 2007])
            ->willReturn($rows);

        $options = $this->makeRepo($conn)->getFilterOptions(2007, null);

        $this->assertSame([['teamid' => 5, 'name' => 'Gallic Warriors']], $options['teams']);
    }

    public function testGetFilterOptionsTeamsFallBackToTeamTableWhenTeamnamesIsEmpty(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchFirstColumn')->willReturn([]);
        $conn->method('fetchAllAssociative')->willReturnOnConsecutiveCalls([], [['teamid' => '3', 'name' => 'Fighting Squirrels (1996)']]);

        $options = $this->makeRepo($conn)->getFilterOptions(2027, null);

        $this->assertSame([['teamid' => 3, 'name' => 'Fighting Squirrels (1996)']], $options['teams']);
    }

    public function testGetFilterOptionsPositionsAreDistinctFromDraftedPlayersOnly(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);
        $conn->method('fetchFirstColumn')->willReturnCallback(function (string $sql) {
            if (str_contains($sql, 'DISTINCT p.pos')) {
                $this->assertStringContainsString("p.pos <> ''", $sql);

                return ['OL', 'QB'];
            }

            return [];
        });

        $options = $this->makeRepo($conn)->getFilterOptions(2019, null);

        $this->assertSame(['OL', 'QB'], $options['positions']);
    }

    public function testGetFilterOptionsNflTeamsUseTheAsOfDateWhenGiven(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);
        $conn->method('fetchFirstColumn')->willReturnCallback(function (string $sql, array $params = []) {
            if (str_contains($sql, 'FROM nflrosters')) {
                $this->assertStringContainsString(':asOf', $sql);
                $this->assertSame(['asOf' => '2019-09-05'], $params);

                return ['GB', 'SEA'];
            }

            return [];
        });

        $options = $this->makeRepo($conn)->getFilterOptions(2019, '2019-09-05');

        $this->assertSame(['GB', 'SEA'], $options['nflTeams']);
    }

    public function testGetFilterOptionsNflTeamsDegradeToTodayWhenAsOfIsNull(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);
        $conn->method('fetchFirstColumn')->willReturnCallback(function (string $sql, array $params = []) {
            if (str_contains($sql, 'FROM nflrosters')) {
                $this->assertStringContainsString('dateoff IS NULL', $sql);
                $this->assertSame([], $params);
            }

            return [];
        });

        $this->makeRepo($conn)->getFilterOptions(2029, null);
    }

    // ---- getSeasonsWithPicks / getSeasonsWithSelections ----

    public function testGetSeasonsWithPicksReturnsAllSeasonsAscendingAsInts(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchFirstColumn')
            ->with($this->stringContains('SELECT DISTINCT Season FROM draftpicks ORDER BY Season'))
            ->willReturn(['2005', '2006', '2029']);

        $this->assertSame([2005, 2006, 2029], $this->makeRepo($conn)->getSeasonsWithPicks());
    }

    public function testGetSeasonsWithSelectionsOnlyIncludesDraftedSeasons(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchFirstColumn')
            ->with($this->stringContains('WHERE playerid IS NOT NULL'))
            ->willReturn(['2006', '2026']);

        $this->assertSame([2006, 2026], $this->makeRepo($conn)->getSeasonsWithSelections());
    }

    // ---- isPlayerAlreadyDrafted ----

    public function testIsPlayerAlreadyDraftedTrueWhenAnotherPickHasThePlayer(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchOne')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('Season = :season'),
                    $this->stringContains('playerid = :playerId'),
                    $this->stringContains('id != :excludeId')
                ),
                ['season' => 2019, 'playerId' => 42, 'excludeId' => 100]
            )
            ->willReturn('1');

        $this->assertTrue($this->makeRepo($conn)->isPlayerAlreadyDrafted(2019, 42, 100));
    }

    public function testIsPlayerAlreadyDraftedFalseWhenNoOtherPickHasThePlayer(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn(false);

        $this->assertFalse($this->makeRepo($conn)->isPlayerAlreadyDrafted(2019, 42, 100));
    }

    // ---- Helpers ----

    private function noFilters(): array
    {
        return ['round' => null, 'pick' => null, 'team' => null, 'pos' => null, 'nfl' => null];
    }

    private function makeRepo(Connection $conn): DraftPickRepository
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($conn);
        $em->method('getClassMetadata')->willReturn(new ClassMetadata(DraftPick::class));

        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new DraftPickRepository($registry);
    }
}
