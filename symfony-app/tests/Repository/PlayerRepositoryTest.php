<?php

namespace App\Tests\Repository;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PlayerRepositoryTest extends TestCase
{
    // ---- getCurrentRoster ----

    public function testGetCurrentRosterReturnsNullForFreeAgent(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(false);

        $repo = $this->makeRepo($conn);

        $this->assertNull($repo->getCurrentRoster(7));
    }

    public function testGetCurrentRosterQueriesOpenRosterSpotForPlayer(): void
    {
        $row = ['team_name' => 'Aardvarks', 'team_id' => 3, 'date_on' => '2024-09-01', 'date_off' => null];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAssociative')
            ->with($this->stringContains('r.DateOff IS NULL'), ['pid' => 7])
            ->willReturn($row);

        $repo = $this->makeRepo($conn);

        $this->assertSame($row, $repo->getCurrentRoster(7));
    }

    // ---- getRosterHistory ----

    public function testGetRosterHistoryQueriesStintsForPlayer(): void
    {
        $rows = [
            ['team_id' => 3, 'date_on' => '2024-09-01', 'date_off' => null, 'team_name' => 'Aardvarks', 'games_activated' => 12, 'active_pts' => 88],
            ['team_id' => 5, 'date_on' => '2022-09-01', 'date_off' => '2023-11-01', 'team_name' => 'Badgers/Cougars', 'games_activated' => 20, 'active_pts' => 140],
        ];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('FROM roster r'),
                    $this->stringContains('GROUP BY r.TeamID, r.DateOn, r.DateOff'),
                    $this->stringContains('ORDER BY r.DateOn DESC')
                ),
                ['pid' => 7]
            )
            ->willReturn($rows);

        $repo = $this->makeRepo($conn);

        $this->assertSame($rows, $repo->getRosterHistory(7));
    }

    public function testGetRosterHistoryReturnsEmptyArrayForNeverRosteredPlayer(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);

        $repo = $this->makeRepo($conn);

        $this->assertSame([], $repo->getRosterHistory(7));
    }

    // ---- getStatsBySeason ----

    public function testGetStatsBySeasonQueriesSeasonTotalsForPlayer(): void
    {
        $rows = [['season' => 2024, 'yards' => 1200, 'weeks_played' => 14, 'total_pts' => 180, 'active_pts' => 120]];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('GROUP BY s.Season'),
                    $this->stringContains('ORDER BY s.Season DESC')
                ),
                ['pid' => 7]
            )
            ->willReturn($rows);

        $repo = $this->makeRepo($conn);

        $this->assertSame($rows, $repo->getStatsBySeason(7));
    }

    public function testGetStatsBySeasonReturnsEmptyArrayForPlayerWithNoStats(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);

        $repo = $this->makeRepo($conn);

        $this->assertSame([], $repo->getStatsBySeason(999));
    }

    // ---- searchPlayers ----

    public function testSearchPlayersDefaultsToActivePlayersPagedAtFifty(): void
    {
        $rows = [['id' => 7, 'lastname' => 'Largent', 'firstname' => 'Steve', 'pos' => 'WR', 'nfl_team' => 'SEA', 'retired' => null, 'wmffl_team' => 'Aardvarks']];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('np.retired IS NULL'),
                    $this->stringContains("LEFT JOIN roster r ON r.PlayerID = np.playerid AND r.DateOff IS NULL"),
                    $this->stringContains('ORDER BY np.lastname, np.firstname'),
                    $this->stringContains('LIMIT 50 OFFSET 0')
                ),
                []
            )
            ->willReturn($rows);

        $repo = $this->makeRepo($conn);

        $this->assertSame($rows, $repo->searchPlayers([], 0));
    }

    public function testSearchPlayersIncludesRetiredWhenInactiveFilterSet(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->logicalNot($this->stringContains('retired IS NULL')), [])
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['inactive' => true], 0);
    }

    public function testSearchPlayersMatchesNameSubstringAgainstBothNameColumns(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('(np.lastname LIKE :q OR np.firstname LIKE :q)'),
                ['q' => '%larg%']
            )
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['q' => 'larg'], 0);
    }

    public function testSearchPlayersEscapesLikeWildcardsInQuery(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->anything(), ['q' => '%50\\%\\_\\\\%'])
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['q' => '50%_\\'], 0);
    }

    public function testSearchPlayersFiltersByWmfflTeamId(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->stringContains('r.TeamID = :team'), ['team' => 3])
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['team' => '3'], 0);
    }

    public function testSearchPlayersFreeAgentsSentinelMatchesNoRosterRow(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->stringContains('r.PlayerID IS NULL'), [])
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['team' => PlayerRepository::FREE_AGENTS], 0);
    }

    public function testSearchPlayersFiltersByNflTeamAndPosition(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('np.pos = :pos'),
                    $this->stringContains('np.team = :nfl')
                ),
                ['pos' => 'K', 'nfl' => 'SEA']
            )
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['nfl' => 'SEA', 'pos' => 'K'], 0);
    }

    public function testSearchPlayersCombinesAllFilters(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('np.retired IS NULL'),
                    $this->stringContains('np.lastname LIKE :q'),
                    $this->stringContains('np.pos = :pos'),
                    $this->stringContains('np.team = :nfl'),
                    $this->stringContains('r.TeamID = :team')
                ),
                ['q' => '%smith%', 'pos' => 'RB', 'nfl' => 'GB', 'team' => 5]
            )
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers(['q' => 'smith', 'team' => '5', 'nfl' => 'GB', 'pos' => 'RB'], 0);
    }

    public function testSearchPlayersOffsetsByPageTimesPerPage(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->stringContains('LIMIT 50 OFFSET 100'), [])
            ->willReturn([]);

        $repo = $this->makeRepo($conn);
        $repo->searchPlayers([], 2);
    }

    // ---- countPlayers ----

    public function testCountPlayersAppliesTheSameFilters(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchOne')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('SELECT COUNT(*)'),
                    $this->stringContains('np.retired IS NULL'),
                    $this->stringContains('np.lastname LIKE :q')
                ),
                ['q' => '%larg%']
            )
            ->willReturn('123');

        $repo = $this->makeRepo($conn);

        $this->assertSame(123, $repo->countPlayers(['q' => 'larg']));
    }

    // ---- dropdown sources ----

    public function testGetDistinctNflTeamsQueriesNonEmptyValues(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchFirstColumn')
            ->with($this->stringContains('SELECT DISTINCT team FROM newplayers'))
            ->willReturn(['GB', 'SEA']);

        $repo = $this->makeRepo($conn);

        $this->assertSame(['GB', 'SEA'], $repo->getDistinctNflTeams());
    }

    public function testGetDistinctPositionsQueriesNonEmptyValues(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchFirstColumn')
            ->with($this->stringContains('SELECT DISTINCT pos FROM newplayers'))
            ->willReturn(['K', 'QB']);

        $repo = $this->makeRepo($conn);

        $this->assertSame(['K', 'QB'], $repo->getDistinctPositions());
    }

    // ---- Helpers ----

    protected function makeRepo(Connection $conn): PlayerRepository
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($conn);
        $em->method('getClassMetadata')->willReturn(new ClassMetadata(Player::class));

        $registry = $this->createStub(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new PlayerRepository($registry);
    }
}
