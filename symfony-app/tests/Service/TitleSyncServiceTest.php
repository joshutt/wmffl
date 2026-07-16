<?php

namespace App\Tests\Service;

use App\Service\TitleSyncService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TitleSyncServiceTest extends TestCase
{
    public function testSyncInsertsMissingAndDeletesStaleForBothTypes(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')->willReturn('1');

        $statements = [];
        $conn->expects($this->exactly(4))->method('executeStatement')
            ->willReturnCallback(function (string $sql, array $params) use (&$statements) {
                $statements[] = [$sql, $params];
                return 0;
            });

        (new TitleSyncService($conn))->syncSeason(2025);

        // champion → League (insert missing, delete stale), then
        // division_winner → Division
        [$insLeague, $delLeague, $insDivision, $delDivision] = $statements;

        $this->assertStringContainsString('INSERT INTO titles', $insLeague[0]);
        $this->assertStringContainsString('sf.champion = 1', $insLeague[0]);
        $this->assertStringContainsString('NOT EXISTS', $insLeague[0]);
        $this->assertSame(['season' => 2025, 'type' => 'League'], $insLeague[1]);

        $this->assertStringContainsString('DELETE t FROM titles t', $delLeague[0]);
        $this->assertStringContainsString('sf.champion = 1', $delLeague[0]);
        $this->assertSame(['season' => 2025, 'type' => 'League'], $delLeague[1]);

        $this->assertStringContainsString('sf.division_winner = 1', $insDivision[0]);
        $this->assertSame(['season' => 2025, 'type' => 'Division'], $insDivision[1]);

        $this->assertStringContainsString('DELETE t FROM titles t', $delDivision[0]);
        $this->assertStringContainsString('sf.division_winner = 1', $delDivision[0]);
        $this->assertSame(['season' => 2025, 'type' => 'Division'], $delDivision[1]);
    }

    public function testSeasonWithoutFlagsIsLeftUntouched(): void
    {
        // Historical seasons (≤2023) predate season_flags; syncing them
        // would wipe their titles, so the guard must bail before any write.
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')->willReturn(false);
        $conn->expects($this->never())->method('executeStatement');

        (new TitleSyncService($conn))->syncSeason(2003);
    }

    public function testToiletTitlesAreNeverTouched(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')->willReturn('1');
        $conn->method('executeStatement')
            ->willReturnCallback(function (string $sql, array $params) {
                $this->assertNotSame('Toilet', $params['type'] ?? null);
                $this->assertStringNotContainsString('Toilet', $sql);
                return 0;
            });

        (new TitleSyncService($conn))->syncSeason(2025);
    }
}
