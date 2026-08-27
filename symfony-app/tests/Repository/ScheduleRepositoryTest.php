<?php

namespace App\Tests\Repository;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class ScheduleRepositoryTest extends TestCase
{
    public function testGetSeasonScheduleJoinsWeekmapAndTeamnames(): void
    {
        $rows = [['week' => 1, 'teama_name' => 'Norsemen']];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('JOIN weekmap w ON s.season = w.season AND s.week = w.week'),
                    $this->stringContains('LEFT JOIN teamnames t1 ON s.teama = t1.teamid AND s.season = t1.season'),
                    $this->stringContains('LEFT JOIN teamnames t2 ON s.teamb = t2.teamid AND s.season = t2.season'),
                    $this->stringContains('ORDER BY s.week, s.label, MD5(CONCAT(t1.name, t2.name))')
                ),
                ['season' => 2024]
            )
            ->willReturn($rows);

        $this->assertSame($rows, $this->makeRepo($conn)->getSeasonSchedule(2024));
    }

    public function testGetByeWeeksExcludesTeamsWithAGame(): void
    {
        $rows = [['nflteam' => 'CIN', 'name' => 'Cincinnati', 'nickname' => 'Bengals', 'week' => 1]];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('LEFT JOIN nflgames g ON g.season = wm.season AND g.week = wm.week'),
                    $this->stringContains('t.nflteam IN ('),
                    $this->stringContains('g.week IS NULL'),
                    $this->stringContains('wm.week > 0'),
                    // seasons with zero nflgames rows (pre-2008) must not
                    // fall through to "every team is on bye every week" -
                    // see the EXISTS guard in the ported query
                    $this->stringContains('EXISTS (SELECT 1 FROM nflgames WHERE season = :season)'),
                    // relocated franchises (nflgames keeps the old code,
                    // nflteams only keeps the current one) must be
                    // translated or the moved team looks perpetually on
                    // bye for every pre-move season
                    $this->stringContains("WHEN 'OAK' THEN 'LV'"),
                    $this->stringContains("WHEN 'SD' THEN 'LAC'"),
                    $this->stringContains("WHEN 'STL' THEN 'LAR'"),
                    $this->stringContains("WHEN 'LA' THEN 'LAR'"),
                    $this->stringContains('ORDER BY wm.week, t.name')
                ),
                ['season' => 2024]
            )
            ->willReturn($rows);

        $this->assertSame($rows, $this->makeRepo($conn)->getByeWeeks(2024));
    }

    public function testHasRowsTrueWhenScheduleRowExists(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchOne')
            ->with($this->stringContains('SELECT 1 FROM schedule WHERE season = :season'), ['season' => 2026])
            ->willReturn('1');

        $this->assertTrue($this->makeRepo($conn)->hasRows(2026));
    }

    public function testHasRowsFalseWhenNoScheduleRows(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn(false);

        $this->assertFalse($this->makeRepo($conn)->hasRows(2026));
    }

    private function makeRepo(Connection $conn): ScheduleRepository
    {
        return new ScheduleRepository($conn);
    }
}
