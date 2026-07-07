<?php

namespace App\Tests\Repository;

use App\Repository\ScoresRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ScoresRepositoryTest extends TestCase
{
    public function testReturnsNullWhenSeasonHasNoVisibleWeek(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(false);
        $conn->expects($this->never())->method('fetchAllAssociative');

        $repo = new ScoresRepository($conn);

        $this->assertNull($repo->getLatestWeekScores(2025));
    }

    public function testReturnsWeekAndGamesForLatestVisibleWeek(): void
    {
        $games = [
            ['teama' => 3, 'leadname' => 'A', 'leadscore' => 90, 'trailname' => 'B', 'trailscore' => 80, 'label' => '', 'overtime' => 0],
        ];

        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')
            ->with($this->anything(), ['season' => 2025])
            ->willReturn(['week' => '5', 'weekname' => 'Week 5']);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->anything(), ['season' => 2025, 'week' => '5'])
            ->willReturn($games);

        $repo = new ScoresRepository($conn);
        $result = $repo->getLatestWeekScores(2025);

        $this->assertSame(5, $result['week']);
        $this->assertSame('Week 5', $result['weekName']);
        $this->assertSame($games, $result['games']);
    }
}
