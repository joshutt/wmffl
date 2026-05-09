<?php

namespace App\Tests\Service;

use App\Service\MvpScoringService;
use PHPUnit\Framework\TestCase;

class MvpScoringServiceTest extends TestCase
{
    private MvpScoringService $service;

    protected function setUp(): void
    {
        $this->service = new MvpScoringService();
    }

    // ---- compare() ----

    public function testCompareReturnsNegativeWhenPlayerScoreIsNegative(): void
    {
        $this->assertSame(-1.0, $this->service->compare(-1.0, 50.0));
    }

    public function testCompareReturnsZeroWhenPlayerEqualsOpponent(): void
    {
        $this->assertSame(0.0, $this->service->compare(50.0, 50.0));
    }

    public function testCompareReturnsZeroWhenPlayerUnderperforms(): void
    {
        $this->assertSame(0.0, $this->service->compare(40.0, 50.0));
    }

    public function testCompareReturnsFullScoreWhenOpponentIsNegative(): void
    {
        $this->assertSame(80.0, $this->service->compare(80.0, -1.0));
    }

    public function testCompareReturnsDifferenceWhenPlayerOutperforms(): void
    {
        $this->assertSame(20.0, $this->service->compare(100.0, 80.0));
    }

    // ---- rankPlayers(): single-player positions (QB, HC, K, OL) ----

    public function testRankPlayersQbOutperformsOpponent(): void
    {
        $rows = [
            $this->row(1, 'QB', 1, 2, 100.0, 'Tom Brady', 'NE', 1),
            $this->row(2, 'QB', 2, 1, 80.0,  'Pat Mahomes', 'KC', 1),
        ];

        $result = $this->service->rankPlayers($rows);

        $this->assertCount(2, $result);
        $this->assertSame('Tom Brady', $result[0]['name']);
        $this->assertEqualsWithDelta(20.0, $result[0]['score'], 0.01);
        $this->assertSame(0.0, $result[1]['score']);
    }

    public function testRankPlayersKickerLogicMatchesQb(): void
    {
        $rows = [
            $this->row(1, 'K', 1, 2, 30.0, 'Kicker A', 'AA', 1),
            $this->row(2, 'K', 2, 1, 20.0, 'Kicker B', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);

        $this->assertEqualsWithDelta(10.0, $result[0]['score'], 0.01);
    }

    public function testRankPlayersHcLogicMatchesQb(): void
    {
        $rows = [
            $this->row(1, 'HC', 1, 2, 50.0, 'Coach A', 'AA', 1),
            $this->row(2, 'HC', 2, 1, 30.0, 'Coach B', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);

        $this->assertEqualsWithDelta(20.0, $result[0]['score'], 0.01);
    }

    // ---- rankPlayers(): defensive positions (DL, LB, DB) ----

    public function testRankPlayersDefensivePlayerComparedToHalfOpponentTotal(): void
    {
        // Team 1 has 1 DB scoring 30; Team 2 has 1 DB scoring 40
        // Team 1 val = compare(30, 40/2) = compare(30, 20) = 10
        // Team 2 val = compare(40, 30/2) = compare(40, 15) = 25
        $rows = [
            $this->row(1, 'DB', 1, 2, 30.0, 'Safety A', 'AA', 1),
            $this->row(2, 'DB', 2, 1, 40.0, 'Safety B', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);

        $this->assertSame('Safety B', $result[0]['name']);
        $this->assertEqualsWithDelta(25.0, $result[0]['score'], 0.01);
        $this->assertSame('Safety A', $result[1]['name']);
        $this->assertEqualsWithDelta(10.0, $result[1]['score'], 0.01);
    }

    public function testRankPlayersLbAndDlUseHalfOpponent(): void
    {
        foreach (['LB', 'DL'] as $pos) {
            $rows = [
                $this->row(1, $pos, 1, 2, 20.0, "Player A $pos", 'AA', 1),
                $this->row(2, $pos, 2, 1, 10.0, "Player B $pos", 'BB', 1),
            ];
            $result = $this->service->rankPlayers($rows);
            // Team 1: compare(20, 10/2=5) = 15
            $this->assertEqualsWithDelta(15.0, $result[0]['score'], 0.01, "Failed for $pos");
        }
    }

    // ---- rankPlayers(): equal-count flex positions ----

    public function testRankPlayersRbEqualCountUsesAverage(): void
    {
        // Team 1: 1 RB scoring 60; Team 2: 1 RB scoring 40
        // val(RB-A) = compare(60, 40/1) = 20; val(RB-B) = compare(40, 60/1) = 0
        $rows = [
            $this->row(1, 'RB', 1, 2, 60.0, 'Back A', 'AA', 1),
            $this->row(2, 'RB', 2, 1, 40.0, 'Back B', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);

        $this->assertSame('Back A', $result[0]['name']);
        $this->assertEqualsWithDelta(20.0, $result[0]['score'], 0.01);
        $this->assertSame(0.0, $result[1]['score']);
    }

    public function testRankPlayersWrEqualCountUsesAverage(): void
    {
        $rows = [
            $this->row(1, 'WR', 1, 2, 80.0, 'Receiver A', 'AA', 1),
            $this->row(2, 'WR', 2, 1, 50.0, 'Receiver B', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);

        $this->assertEqualsWithDelta(30.0, $result[0]['score'], 0.01);
    }

    // ---- rankPlayers(): week-specificity (key bug fix) ----

    public function testRankPlayersUsesWeekSpecificOpponentScores(): void
    {
        // QB on team 1 plays team 2 in week 1 AND week 2 (different games)
        // Week 1: player scores 100, opp scores 80  → val = 20
        // Week 2: player scores 50,  opp scores 60  → val = 0
        // Total: 20
        $rows = [
            $this->row(1, 'QB', 1, 2, 100.0, 'Brady', 'NE', 1),
            $this->row(2, 'QB', 2, 1, 80.0,  'Opp W1', 'KC', 1),
            $this->row(1, 'QB', 1, 2, 50.0,  'Brady', 'NE', 2),
            $this->row(3, 'QB', 2, 1, 60.0,  'Opp W2', 'KC', 2),
        ];

        $result = $this->service->rankPlayers($rows);
        $brady = $this->findByName($result, 'Brady');

        $this->assertEqualsWithDelta(20.0, $brady['score'], 0.01,
            'Week 1 val=20, Week 2 val=0 (underperformed) → total 20');
    }

    // ---- rankPlayers(): multi-week accumulation ----

    public function testRankPlayersAccumulatesAcrossMultipleWeeks(): void
    {
        // Player outperforms by 10 each week for 3 weeks → total 30
        $rows = [];
        for ($week = 1; $week <= 3; $week++) {
            $rows[] = $this->row(1, 'QB', 1, 2, 100.0, 'Player A', 'AA', $week);
            $rows[] = $this->row(2, 'QB', 2, 1, 90.0,  'Player B', 'BB', $week);
        }

        $result = $this->service->rankPlayers($rows);

        $this->assertEqualsWithDelta(30.0, $result[0]['score'], 0.01);
    }

    // ---- rankPlayers(): team abbreviation reflects latest week ----

    public function testRankPlayersAbbrevReflectsLatestWeek(): void
    {
        // Player on team 1 (abbrev 'AA') in week 1, traded to team 3 (abbrev 'CC') by week 2
        $rows = [
            $this->row(1, 'QB', 1, 2, 80.0, 'Traveler', 'AA', 1),
            $this->row(2, 'QB', 2, 1, 60.0, 'Stayer',   'BB', 1),
            $this->row(1, 'QB', 3, 2, 90.0, 'Traveler', 'CC', 2),
            $this->row(2, 'QB', 2, 3, 70.0, 'Stayer',   'BB', 2),
        ];

        $result = $this->service->rankPlayers($rows);
        $traveler = $this->findByName($result, 'Traveler');

        $this->assertSame('CC', $traveler['abbrev'], 'Should show week-2 (latest) team');
    }

    // ---- rankPlayers(): negative (inactive) player score ----

    public function testRankPlayersNegativeScoreDoesNotContributeToPosVal(): void
    {
        // Player B has score -1 (inactive); should not be added to posVal
        // Player A scores 50 vs opponent posVal of 0 (since only inactive player) → compare(50, 0) = 50
        $rows = [
            $this->row(1, 'QB', 1, 2, 50.0, 'Active',   'AA', 1),
            $this->row(2, 'QB', 2, 1, -1.0, 'Inactive', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);
        $active = $this->findByName($result, 'Active');

        // opp posVal is 0 (inactive not counted), compare(50, 0) but opp score negative → returns 50
        $this->assertEqualsWithDelta(50.0, $active['score'], 0.01);
    }

    // ---- rankPlayers(): output structure ----

    public function testRankPlayersResultContainsRequiredKeys(): void
    {
        $rows = [$this->row(1, 'QB', 1, 2, 100.0, 'Brady', 'NE', 1)];

        $result = $this->service->rankPlayers($rows);

        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('pos', $result[0]);
        $this->assertArrayHasKey('abbrev', $result[0]);
        $this->assertArrayHasKey('score', $result[0]);
    }

    public function testRankPlayersScoreIsRoundedToTwoDecimalPlaces(): void
    {
        // 100/3 = 33.333... → compare(50, 33.33) = 16.67
        $rows = [
            $this->row(1, 'RB', 1, 2, 50.0, 'Back A', 'AA', 1),
            $this->row(2, 'RB', 2, 1, 40.0, 'Back B', 'BB', 1),
            $this->row(3, 'RB', 2, 1, 30.0, 'Back C', 'BB', 1),
            $this->row(4, 'RB', 2, 1, 30.0, 'Back D', 'BB', 1),
        ];

        $result = $this->service->rankPlayers($rows);
        $scoreStr = (string) $result[0]['score'];
        $decimals = strlen(substr(strrchr($scoreStr, '.'), 1));

        $this->assertLessThanOrEqual(2, $decimals);
    }

    public function testRankPlayersReturnsEmptyArrayForEmptyInput(): void
    {
        $this->assertSame([], $this->service->rankPlayers([]));
    }

    // ---- flexCompare(): RB mismatched roster groupings ----

    public function testFlexCompareRbUsesPrimaryRbTeGroupWhenCountsMatch(): void
    {
        // Team has 1 RB + 1 TE = 2 flex; opp has 1 RB + 1 TE = 2 flex → primary match
        // opp RB scores 30, opp TE scores 20 → avg = 25; player RB scores 40 → val = 15
        $posVal   = ['RB' => [2 => [1 => 30.0]], 'TE' => [2 => [1 => 20.0]], 'WR' => []];
        $posCount = ['RB' => [1 => [1 => 1], 2 => [1 => 1]], 'TE' => [1 => [1 => 1], 2 => [1 => 1]], 'WR' => []];

        $result = $this->service->flexCompare(40.0, 2, 1, ['RB', 'TE'], ['RB', 'WR'], 1, $posVal, $posCount);

        $this->assertEqualsWithDelta(15.0, $result, 0.01);
    }

    public function testFlexCompareRbUsesSecondaryGroupWhenPrimaryCountsMismatch(): void
    {
        // Team has 2 RB + 0 TE; opp has 1 RB + 1 TE → primary (RB+TE) doesn't match counts
        // Team has 2 RB + 0 WR; opp has 2 RB + 0 WR → secondary match (RB+WR both 2)
        // opp: 2 RB scoring 60 total + 0 WR → avg = 30; player RB scores 40 → val = 10
        $posVal   = ['RB' => [2 => [1 => 60.0]], 'TE' => [2 => [1 => 20.0]], 'WR' => []];
        $posCount = [
            'RB' => [1 => [1 => 2], 2 => [1 => 2]],
            'TE' => [1 => [1 => 0], 2 => [1 => 1]],
            'WR' => [1 => [1 => 0], 2 => [1 => 0]],
        ];

        $result = $this->service->flexCompare(40.0, 2, 1, ['RB', 'TE'], ['RB', 'WR'], 1, $posVal, $posCount);

        $this->assertEqualsWithDelta(10.0, $result, 0.01);
    }

    // ---- Helpers ----

    private function row(
        int $playerId,
        string $pos,
        int $teamId,
        int $opp,
        float $active,
        string $name,
        string $abbrev,
        int $week
    ): array {
        return [
            'playerid' => $playerId,
            'pos'      => $pos,
            'teamid'   => $teamId,
            'opp'      => $opp,
            'active'   => $active,
            'name'     => $name,
            'abbrev'   => $abbrev,
            'week'     => $week,
        ];
    }

    private function findByName(array $result, string $name): array
    {
        foreach ($result as $player) {
            if ($player['name'] === $name) {
                return $player;
            }
        }
        $this->fail("Player '$name' not found in result");
    }
}
