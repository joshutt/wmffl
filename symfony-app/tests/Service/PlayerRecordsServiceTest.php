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

    // ---- mergeRankedList ----

    /** @return array<array{name: string, pts: int}> */
    private static function players(int ...$pts): array
    {
        return array_map(fn($i, $p) => ['name' => "P$i", 'pts' => $p], array_keys($pts), $pts);
    }

    public function testMergeInterleavesSupplementalsAheadOfLowerScores(): void
    {
        $merged = $this->service->mergeRankedList(
            self::players(50, 30, 10, 5),
            [['name' => 'X', 'pts' => 40], ['name' => 'Y', 'pts' => 20]],
            true
        );

        $this->assertSame(
            [['P0', 1], ['X', 2], ['P1', 3], ['Y', 4], ['P2', 5], ['P3', 6]],
            array_map(fn($r) => [$r['name'], $r['rank']], $merged)
        );
    }

    public function testMergeSupplementalTieGoesFirst(): void
    {
        // legacy: extras print while extra.pts >= player.pts, so a tied
        // supplemental precedes the DB row
        $merged = $this->service->mergeRankedList(
            self::players(30),
            [['name' => 'X', 'pts' => 30]],
            false
        );

        $this->assertSame(['X', 'P0'], array_column($merged, 'name'));
    }

    public function testMergeKeepsRowsTyingTheTenthScore(): void
    {
        $merged = $this->service->mergeRankedList(
            self::players(50, 48, 46, 44, 42, 40, 38, 36, 34, 32, 32, 32, 30),
            [],
            true
        );

        // three rows tie the 10th score; the first sub-32 row ends the list
        $this->assertCount(12, $merged);
        $this->assertSame(32, end($merged)['pts']);
    }

    public function testMergeCutoffVariantsDivergeWhenSupplementalLandsPastTen(): void
    {
        // the supplemental becomes the 11th printed row while the 10th
        // DB row (lower-scored) is current: recordseason stops the list
        // without printing the DB row, recordsweek still prints it
        $players = self::players(50, 48, 46, 44, 42, 40, 38, 36, 34, 20);
        $extras = [['name' => 'X', 'pts' => 30]];

        $season = $this->service->mergeRankedList($players, $extras, true);
        $week = $this->service->mergeRankedList($players, $extras, false);

        $this->assertSame('X', end($season)['name']);
        $this->assertSame('P9', end($week)['name']);
        $this->assertCount(count($season) + 1, $week);
    }
}
