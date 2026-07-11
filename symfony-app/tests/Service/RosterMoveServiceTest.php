<?php

namespace App\Tests\Service;

use App\Service\RosterMoveService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class RosterMoveServiceTest extends TestCase
{
    /** Statements recorded inside the write transaction */
    private array $statements = [];

    // ---- executeMoves validation branches ----

    public function testPickupsBlockedInWeek16WaiverPeriod(): void
    {
        $service = $this->makeService(week: 16, isWaiver: true);

        $errors = $service->executeMoves(2, picks: [50], drops: [], waiverPriorities: [], updateWaivers: false);

        $this->assertContains('Pickups are no longer allowed this season', $errors);
        $this->assertSame([], $this->statements);
    }

    public function testWeek16DropsAloneAreStillAllowed(): void
    {
        $service = $this->makeService(week: 16, isWaiver: true, roster: [
            $this->rosterRow(10),
        ], counts: ['total' => 20, 'irplayers' => 0, 'activeplayers' => 20, 'ptsleft' => 10]);

        $errors = $service->executeMoves(2, picks: [], drops: [10], waiverPriorities: [], updateWaivers: false);

        $this->assertSame([], $errors);
        $this->assertNotEmpty($this->statements);
    }

    public function testExceedingThe25ActiveLimitIsRejected(): void
    {
        $service = $this->makeService(counts: ['total' => 25, 'irplayers' => 0, 'activeplayers' => 25, 'ptsleft' => 10]);

        $errors = $service->executeMoves(2, picks: [50], drops: [], waiverPriorities: [], updateWaivers: false);

        $this->assertContains('That would give you 26 players on your roster!!  You must drop someone!!', $errors);
        $this->assertSame([], $this->statements);
    }

    public function testExceedingThe26TotalRosterWithIrIsRejected(): void
    {
        // 24 active + 2 IR: an add stays within the active limit but not the total
        $service = $this->makeService(counts: ['total' => 26, 'irplayers' => 2, 'activeplayers' => 24, 'ptsleft' => 10]);

        $errors = $service->executeMoves(2, picks: [50], drops: [], waiverPriorities: [], updateWaivers: false);

        $this->assertContains('That would give you 27 players, including IR!  You must drop someone!', $errors);
        $this->assertSame([], $this->statements);
    }

    public function testDroppingWhilePickingKeepsTheRosterLegal(): void
    {
        $service = $this->makeService(
            roster: [$this->rosterRow(10)],
            counts: ['total' => 25, 'irplayers' => 0, 'activeplayers' => 25, 'ptsleft' => 10]
        );

        $errors = $service->executeMoves(2, picks: [50], drops: [10], waiverPriorities: [], updateWaivers: false);

        $this->assertSame([], $errors);
    }

    public function testDropsOfPlayersNotOnTheRosterAreIgnoredInCountsAndWrites(): void
    {
        // Roster is full; a forged drop of someone else's player must not
        // free a slot (legacy's UPDATE had no teamid scope)
        $service = $this->makeService(counts: ['total' => 25, 'irplayers' => 0, 'activeplayers' => 25, 'ptsleft' => 10]);

        $errors = $service->executeMoves(2, picks: [50], drops: [999], waiverPriorities: [], updateWaivers: false);

        $this->assertContains('That would give you 26 players on your roster!!  You must drop someone!!', $errors);
    }

    public function testDroppingAnIrPlayerOnlyChangesTheTotalCount(): void
    {
        // 25 active + 1 IR = 26 total. Dropping the IR player makes room
        // for a total-count add but not an active-count add.
        $service = $this->makeService(
            roster: [$this->rosterRow(10, ir: true)],
            counts: ['total' => 26, 'irplayers' => 1, 'activeplayers' => 25, 'ptsleft' => 10]
        );

        $errors = $service->executeMoves(2, picks: [50], drops: [10], waiverPriorities: [], updateWaivers: false);

        $this->assertContains('That would give you 26 players on your roster!!  You must drop someone!!', $errors);
    }

    public function testUnpaidTeamOutOfFreeTransactionsCannotPickUp(): void
    {
        $service = $this->makeService(paid: false, remain: 0);

        $errors = $service->executeMoves(2, picks: [50], drops: [], waiverPriorities: [], updateWaivers: false);

        $this->assertContains("You haven't paid entry fee and are out of free transactions.  No pick-ups allowed.", $errors);
    }

    public function testPaidTeamMayExceedFreeTransactions(): void
    {
        $service = $this->makeService(paid: true, remain: 0);

        $errors = $service->executeMoves(2, picks: [50], drops: [], waiverPriorities: [], updateWaivers: false);

        $this->assertSame([], $errors);
    }

    public function testPickingUpARosteredPlayerIsRejectedByName(): void
    {
        $service = $this->makeService(takenPlayers: [50 => ['firstname' => 'Al', 'lastname' => 'Kaline']]);

        $errors = $service->executeMoves(2, picks: [50], drops: [], waiverPriorities: [], updateWaivers: false);

        $this->assertContains('Al Kaline is already on a roster!!', $errors);
        $this->assertSame([], $this->statements);
    }

    // ---- executeMoves writes ----

    public function testHappyPathWritesRosterTransactionsPointsAndWaivers(): void
    {
        $service = $this->makeService(roster: [$this->rosterRow(10)]);

        $errors = $service->executeMoves(
            2,
            picks: [50],
            drops: [10],
            waiverPriorities: [3 => 60, 1 => 61],
            updateWaivers: true
        );

        $this->assertSame([], $errors);

        $sqls = array_column($this->statements, 'sql');
        $this->assertStringContainsString('UPDATE roster SET DateOff=now()', $sqls[0]);
        $this->assertStringContainsString('teamid = :teamId', $sqls[0]);
        $this->assertSame([10], $this->statements[0]['params']['drops']);

        $this->assertStringContainsString('INSERT INTO roster', $sqls[1]);
        $this->assertSame(50, $this->statements[1]['params']['playerId']);

        $this->assertStringContainsString('UPDATE transpoints SET TransPts', $sqls[2]);
        $this->assertSame(1, $this->statements[2]['params']['count']);

        $this->assertStringContainsString('INSERT INTO transactions', $sqls[3]);
        $this->assertSame('Sign', $this->statements[3]['params']['method']);
        $this->assertStringContainsString('INSERT INTO transactions', $sqls[4]);
        $this->assertSame('Cut', $this->statements[4]['params']['method']);

        $this->assertStringContainsString('DELETE FROM waiverpicks', $sqls[5]);
        // priorities resequenced 1..n in requested order
        $this->assertSame([61, 1], [$this->statements[6]['params']['playerId'], $this->statements[6]['params']['priority']]);
        $this->assertSame([60, 2], [$this->statements[7]['params']['playerId'], $this->statements[7]['params']['priority']]);
    }

    public function testDropOnlyMoveSkipsInsertAndPointsQueries(): void
    {
        $service = $this->makeService(roster: [$this->rosterRow(10)]);

        $errors = $service->executeMoves(2, picks: [], drops: [10], waiverPriorities: [], updateWaivers: false);

        $this->assertSame([], $errors);
        $sqls = array_column($this->statements, 'sql');
        $this->assertCount(2, $sqls);
        $this->assertStringContainsString('UPDATE roster SET DateOff', $sqls[0]);
        $this->assertStringContainsString('INSERT INTO transactions', $sqls[1]);
    }

    public function testClearingAllWaiverPicksDeletesWithoutReinserting(): void
    {
        $service = $this->makeService();

        $errors = $service->executeMoves(2, picks: [], drops: [], waiverPriorities: [], updateWaivers: true);

        $this->assertSame([], $errors);
        $sqls = array_column($this->statements, 'sql');
        $this->assertCount(1, $sqls);
        $this->assertStringContainsString('DELETE FROM waiverpicks', $sqls[0]);
    }

    // ---- searchPlayers ----

    public function testSearchBindsFiltersAndWhitelistsTheSortColumn(): void
    {
        $captured = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = []) use (&$captured) {
                $captured = ['sql' => $sql, 'params' => $params];
                return [];
            }
        );

        (new RosterMoveService($conn))->searchPlayers([
            'last' => "O'Neal", 'first' => 'Sh', 'position' => 'DL',
            'team' => 'SEA', 'available' => 'available', 'order' => 'position',
        ]);

        $this->assertSame("%O'Neal%", $captured['params']['last']);
        $this->assertSame('%Sh%', $captured['params']['first']);
        $this->assertSame('DL', $captured['params']['pos']);
        $this->assertSame('SEA', $captured['params']['team']);
        $this->assertStringNotContainsString("O'Neal", $captured['sql']);
        $this->assertStringEndsWith('ORDER BY pos', $captured['sql']);
    }

    public function testSearchRetiredReplacesTheActiveClause(): void
    {
        $captured = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = []) use (&$captured) {
                $captured = ['sql' => $sql, 'params' => $params];
                return [];
            }
        );

        (new RosterMoveService($conn))->searchPlayers([
            'last' => '', 'first' => '', 'position' => '',
            'team' => 'RET', 'available' => 'taken', 'order' => 'bogus',
        ]);

        $this->assertStringContainsString('p.retired is not null', $captured['sql']);
        $this->assertStringNotContainsString('p.active=1', $captured['sql']);
        $this->assertStringEndsWith('ORDER BY lastname', $captured['sql']);
    }

    // ---- fixture plumbing ----

    private function rosterRow(int $playerid, bool $ir = false, string $pos = 'QB'): array
    {
        return [
            'playerid' => $playerid, 'lastname' => 'Player', 'firstname' => "No$playerid",
            'team' => 'SEA', 'pos' => $pos, 'ir' => $ir ? 'IR' : '',
        ];
    }

    /**
     * Wires a mocked Connection representing one team's state; recorded
     * write statements land in $this->statements.
     *
     * @param array<int, array{firstname: string, lastname: string}> $takenPlayers by playerid
     */
    private function makeService(
        int $week = 5,
        bool $isWaiver = false,
        array $roster = [],
        ?array $counts = null,
        bool $paid = true,
        int $remain = 50,
        array $takenPlayers = []
    ): RosterMoveService {
        $this->statements = [];
        $counts ??= ['total' => 20, 'irplayers' => 1, 'activeplayers' => 19, 'ptsleft' => 30];

        $conn = $this->createMock(Connection::class);

        $conn->method('fetchAssociative')->willReturnCallback(
            function (string $sql, array $params = []) use ($week, $isWaiver, $counts, $paid, $remain, $takenPlayers) {
                if (str_contains($sql, 'FROM weekmap')) {
                    return ['waiverperiod' => $isWaiver ? 1 : 0, 'season' => 2025, 'week' => $week];
                }
                if (str_contains($sql, 'group by t.teamid')) {
                    return ['total' => $counts['total'], 'irplayers' => $counts['irplayers'],
                            'activeplayers' => $counts['activeplayers'], 'ptsleft' => $counts['ptsleft']];
                }
                if (str_contains($sql, 'JOIN paid')) {
                    return ['paid' => $paid ? 1 : 0, 'remain' => $remain];
                }
                if (str_contains($sql, 'r.Playerid = :playerId')) {
                    $taken = $takenPlayers[$params['playerId']] ?? null;
                    return $taken ? ['playerid' => $params['playerId']] + $taken : false;
                }
                return false;
            }
        );

        $conn->method('fetchAllAssociative')->willReturnCallback(
            fn (string $sql) => str_contains($sql, 'order by p.pos, p.lastname') ? $roster : []
        );

        $conn->method('transactional')->willReturnCallback(function (callable $fn) use ($conn) {
            return $fn($conn);
        });

        $conn->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) {
                $this->statements[] = ['sql' => $sql, 'params' => $params];
                return 1;
            }
        );

        return new RosterMoveService($conn);
    }
}
