<?php

namespace App\Tests\Service;

use App\Service\LuckService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LuckServiceTest extends TestCase
{
    public function testPotentialRecordsPlayEveryTeamEveryWeek(): void
    {
        $service = new LuckService($this->createStub(Connection::class));

        [$records, $maxWeek] = $service->computePotentialRecords([
            ['name' => 'A', 'week' => 1, 'off' => 50, 'def' => 20],
            ['name' => 'B', 'week' => 1, 'off' => 40, 'def' => 25],
            ['name' => 'C', 'week' => 1, 'off' => 30, 'def' => 30],
        ]);

        $this->assertSame(1, $maxWeek);
        // A beats B (25 vs 20) and C (20 vs 10); B beats C (10 vs 5)
        $this->assertSame(['win' => 2, 'lose' => 0, 'tie' => 0], $records['A']);
        $this->assertSame(['win' => 1, 'lose' => 1, 'tie' => 0], $records['B']);
        $this->assertSame(['win' => 0, 'lose' => 2, 'tie' => 0], $records['C']);
    }

    public function testEqualMarginsTie(): void
    {
        $service = new LuckService($this->createStub(Connection::class));

        [$records] = $service->computePotentialRecords([
            ['name' => 'A', 'week' => 1, 'off' => 40, 'def' => 20],
            ['name' => 'B', 'week' => 1, 'off' => 40, 'def' => 20],
        ]);

        $this->assertSame(['win' => 0, 'lose' => 0, 'tie' => 1], $records['A']);
        $this->assertSame(['win' => 0, 'lose' => 0, 'tie' => 1], $records['B']);
    }

    public function testActualRecordsTallyGameResults(): void
    {
        $service = new LuckService($this->createStub(Connection::class));

        $records = $service->computeActualRecords([
            ['name' => 'A', 'week' => 1, 'ptsfor' => 80, 'ptsag' => 70],
            ['name' => 'A', 'week' => 2, 'ptsfor' => 60, 'ptsag' => 75],
            ['name' => 'A', 'week' => 3, 'ptsfor' => 50, 'ptsag' => 50],
        ]);

        $this->assertSame(['win' => 1, 'lose' => 1, 'tie' => 1], $records['A']);
    }

    public function testLuckRatingIsActualMinusPotentialPercentage(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturnCallback(function (string $sql) {
            if (str_contains($sql, 'revisedactivations')) {
                // A dominates B on potential
                return [
                    ['name' => 'A', 'week' => 1, 'off' => 50, 'def' => 30],
                    ['name' => 'B', 'week' => 1, 'off' => 20, 'def' => 10],
                ];
            }
            // ...but B won the actual game
            return [
                ['name' => 'A', 'week' => 1, 'ptsfor' => 20, 'ptsag' => 22],
                ['name' => 'B', 'week' => 1, 'ptsfor' => 22, 'ptsag' => 20],
            ];
        });

        $result = (new LuckService($conn))->getLuckRatings(2025);

        // A: potential 1.000, actual 0.000 -> -100; B: the mirror image
        $this->assertSame(['B' => 100.0, 'A' => -100.0], $result['luck']);
        $this->assertSame(1, $result['week']);
        $this->assertSame(100.0, $result['statSig']);
    }
}
