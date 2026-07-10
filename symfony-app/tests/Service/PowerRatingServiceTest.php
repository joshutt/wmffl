<?php

namespace App\Tests\Service;

use App\Service\PowerRatingService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PowerRatingServiceTest extends TestCase
{
    public function testPotentialCountsStartersDepthAndBestFlex(): void
    {
        // One team, one week; rows arrive ordered pos, pts desc
        $rows = [
            $this->row(1, 'A', 'QB', 20, 15),
            $this->row(1, 'A', 'QB', 10, 0),
            $this->row(1, 'A', 'RB', 12, 12),
            $this->row(1, 'A', 'RB', 8, 0),   // flex candidate
            $this->row(1, 'A', 'WR', 9, 9),
            $this->row(1, 'A', 'WR', 7, 7),   // WR2 counts
            $this->row(1, 'A', 'WR', 6, 0),   // WR3 flex candidate, below RB2
            $this->row(1, 'A', 'TE', 5, 5),
            $this->row(1, 'A', 'TE', 4, 0),   // TE2 flex candidate, below RB2
        ];

        [$potential, $actual] = (new PowerRatingService($this->createStub(Connection::class)))
            ->computeWeeklyPoints($rows);

        // QB20 + RB12 + flex(RB2=8) + WR9 + WR7 + TE5 = 61
        $this->assertSame(61, $potential['A'][1]);
        $this->assertSame(48, $actual['A'][1]);
    }

    public function testBetterLateFlexReplacesTheEarlierOne(): void
    {
        $rows = [
            $this->row(1, 'A', 'RB', 12, 12),
            $this->row(1, 'A', 'RB', 8, 0),
            $this->row(1, 'A', 'WR', 9, 9),
            $this->row(1, 'A', 'WR', 7, 7),
            $this->row(1, 'A', 'WR', 10, 0), // hypothetical WR3 above RB2? kept ordered desc in reality
            $this->row(1, 'A', 'TE', 5, 5),
            $this->row(1, 'A', 'TE', 9, 0),
        ];
        // Force ordering as given: TE2 (9) beats RB2 (8): flex swaps 8 out for 9;
        // the WR3 row (10) precedes and would have taken it first — verify swap math
        [$potential] = (new PowerRatingService($this->createStub(Connection::class)))
            ->computeWeeklyPoints($rows);

        // RB12 + flex accumulation + WR9 + WR7 + TE5:
        // flex: RB2=8 -> WR3=10 replaces (+10-8) -> TE2=9 does not beat 10
        $this->assertSame(12 + 9 + 7 + 5 + 10, $potential['A'][1]);
    }

    public function testTeamsAndWeeksAccumulateIndependently(): void
    {
        $rows = [
            $this->row(1, 'A', 'QB', 20, 20),
            $this->row(1, 'B', 'QB', 15, 15),
            $this->row(2, 'A', 'QB', 30, 30),
        ];

        [$potential, $actual] = (new PowerRatingService($this->createStub(Connection::class)))
            ->computeWeeklyPoints($rows);

        $this->assertSame(20, $potential['A'][1]);
        $this->assertSame(15, $potential['B'][1]);
        $this->assertSame(30, $potential['A'][2]);
        $this->assertSame(30, $actual['A'][2]);
    }

    public function testRatingFormulaMatchesLegacyMath(): void
    {
        $service = new PowerRatingService($this->createStub(Connection::class));

        $ratings = $service->computeRatings(
            ['A' => [1 => 60, 2 => 90]],
            ['A' => [1 => 30, 2 => 45]]
        );

        // Week 1: weighted and flat both (60 + 2*30) / 3 = 40
        $this->assertEqualsWithDelta(40.0, $ratings['A'][1], 1e-9);

        // Week 2 by the legacy formula with sqrt(2) weighting
        $sqrt2 = sqrt(2);
        $weighted = ((60 + 90 * $sqrt2) + 2 * (30 + 45 * $sqrt2)) / (3 * (1 + $sqrt2));
        $flat = (150 + 2 * 75) / 6;
        $this->assertEqualsWithDelta(($weighted + $flat) / 2, $ratings['A'][2], 1e-9);
    }

    public function testRatingsSortByLatestWeekAndLinesUseHalfPointSpreads(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(function (string $sql) {
            if (str_contains($sql, 'FROM schedule')) {
                return [['teama' => 'A', 'teamb' => 'B']];
            }
            return [
                $this->row(1, 'A', 'QB', 30, 30),
                $this->row(1, 'B', 'QB', 36, 36),
            ];
        });

        $service = new PowerRatingService($conn);
        $result = $service->getPowerRatings(2025);

        $this->assertSame(1, $result['week']);
        // B outscored A, so B sorts first
        $this->assertSame(['B', 'A'], array_keys($result['ratings']));

        $lines = $service->getLines(2025, 1, $result['ratings']);
        // ratings: A = 30, B = 36 -> diff 6, favorite B
        $this->assertSame([['favorite' => 'B', 'underdog' => 'A', 'line' => 6.0]], $lines);
    }

    private function row(int $week, string $team, string $pos, int|float $pts, int|float $active): array
    {
        return [
            'week' => $week, 'name' => $team, 'firstname' => 'F', 'lastname' => 'L',
            'pos' => $pos, 'pts' => $pts, 'active' => $active,
        ];
    }
}
