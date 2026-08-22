<?php

namespace App\Tests\Repository;

use App\Repository\ActivationRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ActivationRepositoryTest extends TestCase
{
    public function testTheRosterQueryBindsSeasonWeekAndTeamRatherThanInterpolatingThem(): void
    {
        $captured = null;
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params) use (&$captured) {
                $captured = ['sql' => $sql, 'params' => $params];

                return [];
            }
        );

        (new ActivationRepository($conn))->getSubmitRoster(2025, 10, 6);

        $this->assertSame(['season' => 2025, 'week' => 10, 'teamId' => 6], $captured['params']);
        $this->assertStringContainsString('p.lastname', $captured['sql']);
        $this->assertStringNotContainsString('2025', $captured['sql']);
        $this->assertStringNotContainsString('= 6', $captured['sql']);
    }

    /**
     * Requirements Decision 8: the ported reads join no gameplan table,
     * and the legacy opponent-roster query (whose results were computed
     * and then thrown away) is not carried over either.
     */
    public function testNoQueryTouchesTheRetiredSideTables(): void
    {
        $sqls = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql) use (&$sqls) {
                $sqls[] = $sql;

                return [];
            }
        );
        $conn->method('fetchFirstColumn')->willReturnCallback(
            function (string $sql) use (&$sqls) {
                $sqls[] = $sql;

                return [];
            }
        );

        $repo = new ActivationRepository($conn);
        $repo->getSubmitRoster(2025, 10, 6);
        $repo->getPostDeadlineAcquisitions(2025, 6);
        $repo->getActingHeadCoachOptions(2025, 10, 6);
        $repo->getCurrentActivations(2025, 10);
        $repo->getRosteredPlayerIds(2025, 6);
        $repo->getWeekOptions(2025);
        $repo->getAllWeeks(2025);
        $repo->getTeamOptions(2025);

        $this->assertNotEmpty($sqls);
        foreach ($sqls as $sql) {
            $this->assertStringNotContainsStringIgnoringCase('gameplan', $sql);
            $this->assertStringNotContainsStringIgnoringCase('s.TeamA = ', $sql);
        }
    }

    public function testRosterRowsAreNormalizedForTheViews(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([[
            'name' => 'Sam Darnold', 'lastname' => 'Darnold', 'pos' => 'QB', 'nflteamid' => 'SEA',
            'activeId' => '12457', 'kickoff' => '2025-11-09 16:05:00', 'kickoffTs' => '1762722300',
            'homeTeam' => 'SEA', 'roadTeam' => 'ARI', 'playerid' => '12457',
            'status' => 'Questionable', 'details' => 'Shoulder', 'ir' => null,
        ]]);

        $player = (new ActivationRepository($conn))->getSubmitRoster(2025, 10, 6)[0];

        $this->assertSame(12457, $player['playerid']);
        $this->assertSame('Darnold', $player['lastname'], 'the browser re-sorts on the surname the query ordered by');
        $this->assertSame('vs ARI', $player['opp']);
        $this->assertSame(1762722300, $player['kickoffTs']);
        $this->assertTrue($player['active']);
        $this->assertSame('Ques', $player['injuryLabel']);
        $this->assertSame('Questionable: Shoulder', $player['injuryDetail']);
        $this->assertFalse($player['ir']);
    }

    public function testAnUnactivatedPlayerComesBackAsInactive(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([[
            'name' => 'Baker Mayfield', 'pos' => 'QB', 'nflteamid' => 'TB',
            'activeId' => null, 'kickoff' => '2025-11-09 13:00:00', 'kickoffTs' => '1762711200',
            'homeTeam' => 'TB', 'roadTeam' => 'NE', 'playerid' => '12455',
            'status' => null, 'details' => null, 'ir' => null,
        ]]);

        $player = (new ActivationRepository($conn))->getSubmitRoster(2025, 10, 6)[0];

        $this->assertFalse($player['active']);
        $this->assertSame('', $player['injuryLabel']);
    }

    public function testAnIrStintOverridesWhateverTheInjuryReportSays(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([[
            'name' => 'Hurt Guy', 'pos' => 'RB', 'nflteamid' => 'DAL', 'activeId' => null,
            'kickoff' => null, 'kickoffTs' => null, 'homeTeam' => null, 'roadTeam' => null,
            'playerid' => '1', 'status' => 'Questionable', 'details' => 'Knee', 'ir' => '1',
        ]]);

        $player = (new ActivationRepository($conn))->getSubmitRoster(2025, 10, 6)[0];

        $this->assertSame('IR', $player['injuryLabel']);
        $this->assertTrue($player['ir']);
    }

    /** Legacy getPlayerOpp(): a bye, a road game, and no NFL team at all */
    public function testOpponentStringsMatchTheLegacyRules(): void
    {
        $this->assertSame('Bye', ActivationRepository::opponent(
            ['nflteamid' => 'DAL', 'kickoff' => null, 'homeTeam' => null, 'roadTeam' => null]
        ));
        $this->assertSame('@ MIN', ActivationRepository::opponent(
            ['nflteamid' => 'BAL', 'kickoff' => '2025-11-09 13:00:00', 'homeTeam' => 'MIN', 'roadTeam' => 'BAL']
        ));
        $this->assertSame('vs NE', ActivationRepository::opponent(
            ['nflteamid' => 'TB', 'kickoff' => '2025-11-09 13:00:00', 'homeTeam' => 'TB', 'roadTeam' => 'NE']
        ));
        $this->assertSame('', ActivationRepository::opponent(
            ['nflteamid' => '', 'kickoff' => null, 'homeTeam' => null, 'roadTeam' => null]
        ));
    }

    public function testPostDeadlineAcquisitionsComeBackAsUniqueInts(): void
    {
        $captured = null;
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchFirstColumn')->willReturnCallback(
            function (string $sql, array $params) use (&$captured) {
                $captured = $params;

                return ['12', '12', '77'];
            }
        );

        $ids = (new ActivationRepository($conn))->getPostDeadlineAcquisitions(2025, 6);

        $this->assertSame([12, 77], $ids);
        $this->assertSame(14, $captured['lockWeek']);
    }

    public function testWeekOptionsAreTypedForThePicker(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            ['week' => '3', 'weekname' => 'Week 3'],
            ['week' => '4', 'weekname' => null],
        ]);

        $repo = new ActivationRepository($conn);
        $expected = [['week' => 3, 'weekname' => 'Week 3'], ['week' => 4, 'weekname' => '']];

        $this->assertSame($expected, $repo->getWeekOptions(2025));
        $this->assertSame($expected, $repo->getAllWeeks(2025));
    }

    /**
     * The submit form only offers weeks still open; the lineup view and
     * the admin override look backwards too.
     */
    public function testOnlyTheSubmitPickerIsLimitedToOpenWeeks(): void
    {
        $sqls = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql) use (&$sqls) {
                $sqls[] = $sql;

                return [];
            }
        );

        $repo = new ActivationRepository($conn);
        $repo->getWeekOptions(2025);
        $repo->getAllWeeks(2025);

        $this->assertStringContainsString('EndDate > now()', $sqls[0]);
        $this->assertStringNotContainsString('EndDate', $sqls[1]);
    }
}
