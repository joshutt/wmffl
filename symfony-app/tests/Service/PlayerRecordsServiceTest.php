<?php

namespace App\Tests\Service;

use App\Service\PlayerRecordsService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class PlayerRecordsServiceTest extends TestCase
{
    private PlayerRecordsService $service;

    protected function setUp(): void
    {
        $this->service = new PlayerRecordsService($this->createStub(Connection::class));
    }

    public function testBeatingTheTopThresholdTakesRankOne(): void
    {
        $records = $this->service->rankAgainstThresholds('QB', [50, 40, 30], [
            ['name' => 'Al', 'pts' => 60, 'week' => 'Week 3'],
        ]);

        $this->assertCount(1, $records);
        $this->assertSame(1, $records[0]['rank']);
        $this->assertSame('Al', $records[0]['name']);
        $this->assertSame('Week 3', $records[0]['week']);
        $this->assertFalse($records[0]['tie']);
    }

    public function testMatchingAThresholdIsATie(): void
    {
        $records = $this->service->rankAgainstThresholds('QB', [50, 40, 30], [
            ['name' => 'Al', 'pts' => 40],
        ]);

        $this->assertSame(2, $records[0]['rank']);
        $this->assertTrue($records[0]['tie']);
    }

    public function testLaterPerformancesArePushedDownByEarlierOnes(): void
    {
        $records = $this->service->rankAgainstThresholds('QB', [50, 40, 30], [
            ['name' => 'Al', 'pts' => 60],
            ['name' => 'Bo', 'pts' => 55],
            ['name' => 'Cy', 'pts' => 35],
        ]);

        // Al and Bo both beat the old #1; Cy only beats the old #3 but two
        // new scores sit above it
        $this->assertSame([1, 2, 5], array_column($records, 'rank'));
    }

    public function testEqualScoresShareARank(): void
    {
        $records = $this->service->rankAgainstThresholds('QB', [50, 40, 30], [
            ['name' => 'Al', 'pts' => 45],
            ['name' => 'Bo', 'pts' => 45],
        ]);

        $this->assertSame([2, 2], array_column($records, 'rank'));
    }

    public function testEverythingPastRankTenIsCut(): void
    {
        $thresholds = array_fill(0, 12, 10); // twelve slots all requiring 10+
        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $rows[] = ['name' => "P$i", 'pts' => 50 - $i];
        }

        $records = $this->service->rankAgainstThresholds('RB', $thresholds, $rows);

        $this->assertCount(10, $records);
        $this->assertSame(range(1, 10), array_column($records, 'rank'));
    }

    public function testFirstNonQualifyingScoreEndsThePosition(): void
    {
        $records = $this->service->rankAgainstThresholds('QB', [50, 40, 30], [
            ['name' => 'Al', 'pts' => 60],
            ['name' => 'Bo', 'pts' => 20],
            ['name' => 'Cy', 'pts' => 60], // never reached (input is sorted desc in practice)
        ]);

        $this->assertSame(['Al'], array_column($records, 'name'));
    }
}
