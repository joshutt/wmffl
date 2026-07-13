<?php

namespace App\Tests\Repository;

use App\Repository\TradeOfferRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TradeOfferRepositoryTest extends TestCase
{
    // ---- findOffer ----

    public function testFindOfferReturnsFullTermsKeyedByGivingTeam(): void
    {
        $repo = $this->makeRepository(
            offerRow: $this->offerRow(date: '-2 days'),
            players: [
                ['TeamFromID' => 2, 'playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'team' => 'DET'],
                ['TeamFromID' => 5, 'playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'team' => 'LV'],
            ],
            picks: [
                ['TeamFromID' => 2, 'Season' => 2027, 'Round' => 1, 'orgTeamId' => 7, 'orgTeamName' => 'Third Team'],
            ],
            points: [
                ['TeamFromID' => 5, 'Season' => 2026, 'Points' => 5],
            ]
        );

        $offer = $repo->findOffer(100);

        $this->assertSame(100, $offer['offerId']);
        $this->assertSame('Pending', $offer['status']);
        $this->assertSame(2, $offer['teamAId']);
        $this->assertSame('Mustangs', $offer['teamAName']);
        $this->assertSame('Rhinos', $offer['teamBName']);

        $this->assertSame(
            [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']],
            $offer['terms'][2]['players']
        );
        $this->assertSame(
            [['season' => 2027, 'round' => 1, 'orgTeamId' => 7, 'orgTeamName' => 'Third Team']],
            $offer['terms'][2]['picks']
        );
        $this->assertSame([], $offer['terms'][2]['points']);

        $this->assertSame(
            [['playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'nflteam' => 'LV']],
            $offer['terms'][5]['players']
        );
        $this->assertSame([['season' => 2026, 'points' => 5]], $offer['terms'][5]['points']);
    }

    public function testPickQueryFallsBackToGivingTeamWhenOrgTeamIsNull(): void
    {
        $captured = [];
        $repo = $this->makeRepository(offerRow: $this->offerRow(), capturedSql: $captured);

        $repo->findOffer(100);

        $pickSql = $captured[1]['sql'];
        $this->assertStringContainsString('COALESCE(op.OrgTeam, op.TeamFromID)', $pickSql);
    }

    public function testPendingOfferOlderThanSevenDaysReadsAsExpired(): void
    {
        $repo = $this->makeRepository(offerRow: $this->offerRow(date: '-8 days'));

        $offer = $repo->findOffer(100);

        $this->assertSame('Expired', $offer['status']);
    }

    public function testPendingOfferSixDaysOldIsStillPending(): void
    {
        $repo = $this->makeRepository(offerRow: $this->offerRow(date: '-6 days'));

        $offer = $repo->findOffer(100);

        $this->assertSame('Pending', $offer['status']);
        $this->assertEquals(
            $offer['date']->modify('+7 days'),
            $offer['expires']
        );
    }

    public function testSettledOfferIsNotRelabelledExpired(): void
    {
        $repo = $this->makeRepository(offerRow: $this->offerRow(date: '-30 days', status: 'Accept'));

        $offer = $repo->findOffer(100);

        $this->assertSame('Accept', $offer['status']);
    }

    public function testFindOfferReturnsNullForUnknownId(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(false);

        $repo = new TradeOfferRepository($conn);

        $this->assertNull($repo->findOffer(999));
    }

    // ---- whose move (LastOfferID stores a TEAM id) ----

    public function testItIsTheOtherTeamsMoveWhenILastOffered(): void
    {
        $repo = $this->makeRepository(offerRow: $this->offerRow(lastOfferTeamId: 2));
        $offer = $repo->findOffer(100);

        $this->assertFalse($repo->isTeamsMove($offer, 2), 'offering team must wait');
        $this->assertTrue($repo->isTeamsMove($offer, 5), 'receiving team may act');
    }

    public function testItIsMyMoveWhenTheOtherTeamLastOffered(): void
    {
        $repo = $this->makeRepository(offerRow: $this->offerRow(lastOfferTeamId: 5));
        $offer = $repo->findOffer(100);

        $this->assertTrue($repo->isTeamsMove($offer, 2));
        $this->assertFalse($repo->isTeamsMove($offer, 5));
    }

    // ---- comment history across the PrevOfferID chain ----

    public function testCommentHistoryWalksThePrevOfferChainChronologically(): void
    {
        $conn = $this->createMock(Connection::class);
        // offer 102 <- 101 <- 100 (root)
        $conn->method('fetchOne')->willReturnCallback(
            fn (string $sql, array $params) => match ($params['id']) {
                102 => 101,
                101 => 100,
                100 => null,
            }
        );

        $captured = null;
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = [], array $types = []) use (&$captured) {
                $captured = $params;
                return [
                    ['TeamID' => 2, 'teamName' => 'Mustangs', 'Action' => 'offered',
                        'Date' => '2026-07-01 10:00:00', 'Comment' => 'First offer'],
                    ['TeamID' => 5, 'teamName' => 'Rhinos', 'Action' => 'countered',
                        'Date' => '2026-07-02 11:00:00', 'Comment' => 'How about this'],
                    ['TeamID' => 2, 'teamName' => 'Mustangs', 'Action' => 'amended',
                        'Date' => '2026-07-03 12:00:00', 'Comment' => 'Final answer'],
                ];
            }
        );

        $repo = new TradeOfferRepository($conn);
        $history = $repo->getCommentHistory(102);

        $this->assertSame([102, 101, 100], $captured['ids']);
        $this->assertCount(3, $history);
        $this->assertSame('offered', $history[0]['action']);
        $this->assertSame('Mustangs', $history[0]['teamName']);
        $this->assertEquals(new \DateTimeImmutable('2026-07-01 10:00:00'), $history[0]['date']);
        $this->assertSame('Final answer', $history[2]['comment']);
    }

    public function testCommentHistorySurvivesAPrevOfferCycle(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')->willReturnCallback(
            fn (string $sql, array $params) => match ($params['id']) {
                102 => 101,
                101 => 102, // corrupt: points back at its successor
            }
        );
        $captured = null;
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = [], array $types = []) use (&$captured) {
                $captured = $params;
                return [];
            }
        );

        $repo = new TradeOfferRepository($conn);
        $repo->getCommentHistory(102);

        $this->assertSame([102, 101], $captured['ids']);
    }

    // ---- builder data ----

    public function testTradeableRosterExcludesHeadCoachesInSql(): void
    {
        $captured = null;
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = []) use (&$captured) {
                $captured = $sql;
                return [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'team' => 'DET']];
            }
        );

        $repo = new TradeOfferRepository($conn);
        $roster = $repo->getTradeableRoster(2);

        $this->assertStringContainsString("p.pos <> 'HC'", $captured);
        $this->assertStringContainsString('r.dateoff IS NULL', $captured);
        $this->assertSame(
            [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']],
            $roster
        );
    }

    public function testOwnedFuturePicksAreUnusedAndOrgTeamAlwaysResolved(): void
    {
        $captured = null;
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = []) use (&$captured) {
                $captured = $sql;
                return [['id' => 9, 'season' => 2027, 'round' => 3, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs']];
            }
        );

        $repo = new TradeOfferRepository($conn);
        $picks = $repo->getOwnedFuturePicks(2, 2027);

        $this->assertStringContainsString('d.playerid IS NULL', $captured);
        $this->assertStringContainsString('COALESCE(d.orgTeam, d.teamid)', $captured);
        $this->assertSame(
            [['id' => 9, 'season' => 2027, 'round' => 3, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs']],
            $picks
        );
    }

    public function testPointsBalancesFillMissingSeasonsWithZero(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            ['season' => 2026, 'remaining' => 12],
            ['season' => 2028, 'remaining' => 30],
        ]);

        $repo = new TradeOfferRepository($conn);

        $this->assertSame(
            [2026 => 12, 2027 => 0, 2028 => 30],
            $repo->getPointsBalances(2, 2026, 2028)
        );
    }

    // ---- helpers ----

    private function offerRow(
        string $date = '-1 day',
        string $status = 'Pending',
        int $lastOfferTeamId = 2
    ): array {
        return [
            'OfferID' => 100,
            'TeamAID' => 2,
            'TeamBID' => 5,
            'Status' => $status,
            'Date' => (new \DateTimeImmutable($date))->format('Y-m-d H:i:s'),
            'LastOfferID' => $lastOfferTeamId,
            'PrevOfferID' => null,
            'teamAName' => 'Mustangs',
            'teamBName' => 'Rhinos',
        ];
    }

    /**
     * Connection stub for findOffer(): fetchAssociative -> the offer row,
     * fetchAllAssociative -> players, picks, points in call order.
     */
    private function makeRepository(
        array $offerRow,
        array $players = [],
        array $picks = [],
        array $points = [],
        ?array &$capturedSql = null
    ): TradeOfferRepository {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn($offerRow);

        $termResults = [$players, $picks, $points];
        $call = 0;
        $conn->method('fetchAllAssociative')->willReturnCallback(
            function (string $sql, array $params = []) use (&$termResults, &$call, &$capturedSql) {
                if ($capturedSql !== null || func_num_args() > 0) {
                    $capturedSql[] = ['sql' => $sql, 'params' => $params];
                }
                return $termResults[$call++] ?? [];
            }
        );

        return new TradeOfferRepository($conn);
    }
}
