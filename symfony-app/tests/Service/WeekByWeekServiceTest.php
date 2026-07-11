<?php

namespace App\Tests\Service;

use App\Service\WeekByWeekService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class WeekByWeekServiceTest extends TestCase
{
    public function testTeamGridPivotsWeeksAndFillsGaps(): void
    {
        $service = new WeekByWeekService($this->connectionReturning([
            $this->row(7, 'Steve', 'Largent', 'WR', 1, 12),
            $this->row(7, 'Steve', 'Largent', 'WR', 3, 9),   // week 2 missing
            $this->row(9, 'Jim', 'Zorn', 'QB', 2, 15),
        ]));

        $grid = $service->getTeamGrid(2025, 2);

        $this->assertSame(['Name', 'Pos', 'NFL', 'Team', 1, 2, 3, 'Tot'], $grid['titles']);
        $this->assertSame(['Steve Largent', 'WR', 'SEA', 'AE', 12, null, 9, 21], $grid['rows'][0]);
        $this->assertSame(['Jim Zorn', 'QB', 'SEA', 'AE', null, 15, null, 15], $grid['rows'][1]);
    }

    public function testPlayerWithNoScoresGetsANullWeekOneRow(): void
    {
        $service = new WeekByWeekService($this->connectionReturning([
            $this->row(7, 'Steve', 'Largent', 'WR', null, null),
        ]));

        $grid = $service->getTeamGrid(2025, 2);

        $this->assertSame(['Steve Largent', 'WR', 'SEA', 'AE', null, 0], $grid['rows'][0]);
    }

    public function testPositionGridDropsScorelessPlayersAndSortsByTotal(): void
    {
        $service = new WeekByWeekService($this->connectionReturning([
            $this->row(7, 'Steve', 'Largent', 'WR', 1, 12),
            $this->row(8, 'Brian', 'Blades', 'WR', 1, 20),
            $this->row(9, 'No', 'Show', 'WR', null, null),
        ]));

        $grid = $service->getPositionGrid(2025, 'WR');

        $this->assertSame(['Brian Blades', 'Steve Largent'], array_column($grid['rows'], 0));
    }

    private function connectionReturning(array $rows): Connection
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn($rows);

        return $conn;
    }

    private function row(int $id, string $first, string $last, string $pos, ?int $week, ?int $pts): array
    {
        return [
            'playerid' => $id, 'firstname' => $first, 'lastname' => $last, 'pos' => $pos,
            'season' => 2025, 'week' => $week, 'pts' => $pts, 'abbrev' => 'AE', 'nfl' => 'SEA',
        ];
    }
}
