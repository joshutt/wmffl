<?php

namespace App\Tests\Service;

use App\Service\TradeExecutionService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TradeExecutionServiceTest extends TestCase
{
    /** Statements recorded inside the execute() transaction */
    private array $statements = [];
    private bool $inTransaction = false;

    // ---- validateAcceptance ----

    public function testFreshOfferValidates(): void
    {
        $service = $this->makeService();

        $this->assertSame([], $service->validateAcceptance($this->offer()));
    }

    public function testRosterMatchAtIndexZeroValidates(): void
    {
        // Legacy !array_search() read a hit at index 0 as a failure; the
        // first player returned by the roster query must pass
        $service = $this->makeService(rosterByTeam: [2 => [50], 5 => [60]]);

        $this->assertSame([], $service->validateAcceptance($this->offer()));
    }

    public function testPlayerSinceDroppedFailsValidation(): void
    {
        $service = $this->makeService(rosterByTeam: [2 => [], 5 => [60]]);

        $errors = $service->validateAcceptance($this->offer());

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Al Kaline is no longer on the Mustangs roster', $errors[0]);
    }

    public function testPickSinceMovedFailsValidation(): void
    {
        $service = $this->makeService(pickCount: 0);

        $errors = $service->validateAcceptance($this->offer());

        $this->assertStringContainsString('no longer belongs to the Mustangs', $errors[0]);
    }

    public function testPointsSinceSpentFailValidation(): void
    {
        $service = $this->makeService(pointsRemaining: 3);

        $errors = $service->validateAcceptance($this->offer());

        $this->assertStringContainsString('no longer have 5 points to give in 2026', $errors[0]);
    }

    public function testMissingGiverPointsRowFailsValidation(): void
    {
        $service = $this->makeService(pointsRemaining: false);

        $errors = $service->validateAcceptance($this->offer());

        $this->assertStringContainsString('no longer have 5 points', $errors[0]);
    }

    public function testMissingReceiverPointsRowFailsValidation(): void
    {
        $service = $this->makeService(receiverHasPointsRow: false);

        $errors = $service->validateAcceptance($this->offer());

        $this->assertStringContainsString('Rhinos cannot receive points for 2026', $errors[0]);
    }

    // ---- execute ----

    public function testExecuteWritesEveryTradeSideEffectInsideOneTransaction(): void
    {
        $service = $this->makeService();

        $service->execute($this->offer(), 5, 'Done deal');

        $sqls = array_column($this->statements, 'sql');

        // 1. offer settled
        $this->assertStringContainsString("UPDATE offer SET Status = 'Accept'", $sqls[0]);
        $this->assertSame(100, $this->statements[0]['params']['offerId']);

        // 2. team 2's players: closed on 2, opened on 5, trade row
        $this->assertStringContainsString('UPDATE roster SET Dateoff = now()', $sqls[1]);
        $this->assertSame(2, $this->statements[1]['params']['teamId']);
        $this->assertSame([50], $this->statements[1]['params']['playerIds']);
        $this->assertStringContainsString('INSERT INTO roster', $sqls[2]);
        $this->assertSame(['playerId' => 50, 'teamId' => 5], $this->statements[2]['params']);
        $this->assertStringContainsString('INSERT INTO trade', $sqls[3]);
        $this->assertSame(
            ['fromId' => 2, 'toId' => 5, 'playerId' => 50, 'tradeGroup' => 43],
            $this->statements[3]['params'],
            'trade rows share the fresh TradeGroup (max+1)'
        );

        // 3. team 2's pick moves to 5, provenance stamped
        $this->assertStringContainsString('UPDATE draftpicks SET teamid = :toId, orgTeam = :orgTeamId', $sqls[4]);
        $this->assertSame(
            ['toId' => 5, 'orgTeamId' => 2, 'season' => 2027, 'round' => 1, 'fromId' => 2],
            $this->statements[4]['params']
        );

        // 4. team 2's points: -= on 2, += on 5
        $this->assertStringContainsString('TotalPts = TotalPts - :points', $sqls[5]);
        $this->assertSame(['points' => 5, 'teamId' => 2, 'season' => 2026], $this->statements[5]['params']);
        $this->assertStringContainsString('TotalPts = TotalPts + :points', $sqls[6]);
        $this->assertSame(['points' => 5, 'teamId' => 5, 'season' => 2026], $this->statements[6]['params']);

        // 5. team 2's picks+points summary sentence (legacy wording)
        $this->assertStringContainsString('INSERT INTO trade', $sqls[7]);
        $this->assertSame(
            'a 1st round pick in 2027 and 5 protection points in 2026 ',
            $this->statements[7]['params']['summary']
        );
        $this->assertSame(43, $this->statements[7]['params']['tradeGroup']);

        // 6. team 5's players cross the other way
        $this->assertStringContainsString('UPDATE roster SET Dateoff = now()', $sqls[8]);
        $this->assertSame(5, $this->statements[8]['params']['teamId']);
        $this->assertSame(['playerId' => 60, 'teamId' => 2], $this->statements[9]['params']);
        $this->assertSame(
            ['fromId' => 5, 'toId' => 2, 'playerId' => 60, 'tradeGroup' => 43],
            $this->statements[10]['params']
        );

        // 7. the two 'Trade' marker rows (legacy playerid=1 quirk)
        $this->assertStringContainsString('INSERT INTO transactions', $sqls[11]);
        $this->assertStringContainsString("1, 'Trade'", $sqls[11]);
        $this->assertSame(2, $this->statements[11]['params']['teamId']);
        $this->assertSame(5, $this->statements[12]['params']['teamId']);

        // 8. the accepted comment
        $this->assertStringContainsString('INSERT INTO offercomments', $sqls[13]);
        $this->assertSame(
            ['offerId' => 100, 'teamId' => 5, 'action' => 'accepted', 'comment' => 'Done deal'],
            $this->statements[13]['params']
        );

        $this->assertCount(14, $this->statements, 'no writes outside the expected set');
    }

    public function testEmptyCommentWritesNoCommentRow(): void
    {
        $service = $this->makeService();

        $service->execute($this->offer(), 5, '   ');

        foreach (array_column($this->statements, 'sql') as $sql) {
            $this->assertStringNotContainsString('offercomments', $sql);
        }
    }

    public function testMidExecutionFailurePropagatesOutOfTheTransaction(): void
    {
        // The real Connection::transactional rolls back when the callback
        // throws; here we assert a mid-write failure escapes execute()
        // instead of being swallowed after partial work
        $service = $this->makeService(failOn: 'INSERT INTO trade');

        $this->expectException(\RuntimeException::class);
        $service->execute($this->offer(), 5, '');
    }

    public function testAllWritesHappenInsideTheTransactionalCallback(): void
    {
        $service = $this->makeService();

        $service->execute($this->offer(), 5, 'x');

        foreach ($this->statements as $statement) {
            $this->assertTrue($statement['inTransaction'], "outside transaction: {$statement['sql']}");
        }
    }

    // ---- helpers ----

    /**
     * Offer: team 2 (Mustangs) gives Al Kaline + 2027 1st (own) + 5 pts
     * 2026; team 5 (Rhinos) gives Bo Jackson.
     */
    private function offer(): array
    {
        return [
            'offerId' => 100,
            'teamAId' => 2,
            'teamAName' => 'Mustangs',
            'teamBId' => 5,
            'teamBName' => 'Rhinos',
            'status' => 'Pending',
            'lastOfferTeamId' => 2,
            'terms' => [
                2 => [
                    'players' => [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']],
                    'picks' => [['season' => 2027, 'round' => 1, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs']],
                    'points' => [['season' => 2026, 'points' => 5]],
                ],
                5 => [
                    'players' => [['playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'nflteam' => 'LV']],
                    'picks' => [],
                    'points' => [],
                ],
            ],
        ];
    }

    /**
     * @param array<int, int[]> $rosterByTeam current roster playerids per team
     * @param int|false $pointsRemaining giver's remaining balance (false = no row)
     */
    private function makeService(
        array $rosterByTeam = [2 => [50], 5 => [60]],
        int $pickCount = 1,
        int|false $pointsRemaining = 12,
        bool $receiverHasPointsRow = true,
        ?string $failOn = null
    ): TradeExecutionService {
        $conn = $this->createMock(Connection::class);

        $conn->method('fetchFirstColumn')->willReturnCallback(
            static fn (string $sql, array $params) => $rosterByTeam[$params['teamId']] ?? []
        );
        $conn->method('fetchOne')->willReturnCallback(
            function (string $sql, array $params = []) use ($pickCount, $pointsRemaining, $receiverHasPointsRow) {
                if (str_contains($sql, 'FROM draftpicks')) {
                    return $pickCount;
                }
                if (str_contains($sql, 'TotalPts - ProtectionPts')) {
                    return $pointsRemaining;
                }
                if (str_contains($sql, 'FROM transpoints')) {
                    return $receiverHasPointsRow ? 1 : 0;
                }
                if (str_contains($sql, 'MAX(tradegroup)')) {
                    return 43;
                }
                return false;
            }
        );
        $conn->method('transactional')->willReturnCallback(
            function (callable $callback) use ($conn) {
                $this->inTransaction = true;
                try {
                    return $callback($conn);
                } finally {
                    $this->inTransaction = false;
                }
            }
        );
        $conn->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use ($failOn) {
                if ($failOn !== null && str_contains($sql, $failOn)) {
                    throw new \RuntimeException('forced mid-execution failure');
                }
                $this->statements[] = [
                    'sql' => $sql,
                    'params' => $params,
                    'inTransaction' => $this->inTransaction,
                ];
                return 1;
            }
        );

        return new TradeExecutionService($conn);
    }
}
