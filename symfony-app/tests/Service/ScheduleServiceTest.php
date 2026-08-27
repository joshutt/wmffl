<?php

namespace App\Tests\Service;

use App\Repository\ScheduleRepository;
use App\Service\ScheduleService;
use App\Service\SeasonWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ScheduleServiceTest extends TestCase
{
    // ---- resolveDefaultSeason ----

    public function testResolveDefaultSeasonUsesCurrentSeasonWhenItHasRows(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('hasRows')->willReturn(true);

        $this->assertSame(2026, $this->makeService($repo, currentSeason: 2026)->resolveDefaultSeason());
    }

    public function testResolveDefaultSeasonFallsBackToPreviousSeasonWhenEmpty(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('hasRows')->willReturn(false);

        $this->assertSame(2025, $this->makeService($repo, currentSeason: 2026)->resolveDefaultSeason());
    }

    // ---- getSchedule: post-2000 season, dates + byes ----

    public function testGetScheduleRendersDatesAndByesForAModernSeason(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('getSeasonSchedule')->willReturn([
            $this->row(['week' => 1, 'displayDate' => '2024-09-08']),
            $this->row(['week' => 2, 'displayDate' => '2024-09-15']),
        ]);
        $repo->method('getByeWeeks')->willReturn([
            ['nflteam' => 'CIN', 'name' => 'Cincinnati', 'nickname' => 'Bengals', 'week' => 1],
            ['nflteam' => 'NYJ', 'name' => 'New York', 'nickname' => 'Jets', 'week' => 1],
        ]);

        $schedule = $this->makeService($repo, currentSeason: 2024, currentWeek: 5)->getSchedule(2024);

        $this->assertCount(2, $schedule['weeks']);
        $this->assertSame('Sunday, September 8', $schedule['weeks'][0]['dateLine']);
        $this->assertSame('Cincinnati, New York Jets', $schedule['weeks'][0]['byes']);
        $this->assertNull($schedule['weeks'][1]['byes']);
    }

    // ---- getSchedule: pre-2000 season, zero-date guard + empty byes ----

    public function testGetScheduleGuardsZeroDatesAndHasNoByesForPre2000Season(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('getSeasonSchedule')->willReturn([
            // weekmap.displayDate is a DATETIME column; its zero value is
            // "0000-00-00 00:00:00", not the bare-date "0000-00-00".
            $this->row(['week' => 1, 'displayDate' => '0000-00-00 00:00:00', 'weekname' => 'Week 1']),
        ]);
        $repo->method('getByeWeeks')->willReturn([]);

        $schedule = $this->makeService($repo, currentSeason: 2026)->getSchedule(1995);

        $this->assertNull($schedule['weeks'][0]['dateLine']);
        $this->assertSame('Week 1', $schedule['weeks'][0]['heading']);
        $this->assertNull($schedule['weeks'][0]['byes']);
    }

    // ---- getSchedule: postseason label grouping ----

    public function testGetScheduleGroupsPostseasonRoundsSharingAWeekByLabel(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('getSeasonSchedule')->willReturn([
            $this->row(['week' => 15, 'label' => 'Wild Card', 'weekname' => 'Week 15']),
            $this->row(['week' => 15, 'label' => 'Wild Card', 'weekname' => 'Week 15']),
            $this->row(['week' => 15, 'label' => 'Toilet Bowl', 'weekname' => 'Week 15']),
        ]);
        $repo->method('getByeWeeks')->willReturn([]);

        $schedule = $this->makeService($repo, currentSeason: 2026)->getSchedule(2024);

        $this->assertCount(2, $schedule['weeks']);
        $this->assertSame('Wild Card', $schedule['weeks'][0]['heading']);
        $this->assertCount(2, $schedule['weeks'][0]['games']);
        $this->assertSame('Toilet Bowl', $schedule['weeks'][1]['heading']);
        $this->assertCount(1, $schedule['weeks'][1]['games']);

        // both groups share a week number and weekname; the quick-jump
        // anchor must follow the label instead or the two collide
        $this->assertNotSame($schedule['weeks'][0]['anchor'], $schedule['weeks'][1]['anchor']);
        $this->assertSame('WildCard', $schedule['weeks'][0]['anchor']);
        $this->assertSame('ToiletBowl', $schedule['weeks'][1]['anchor']);
    }

    // ---- getSchedule: winner ordering + past/upcoming ----

    public function testGetScheduleListsWinnerFirstWithScoresForPastWeeks(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('getSeasonSchedule')->willReturn([
            $this->row(['week' => 1, 'teama_name' => 'Norsemen', 'teama_id' => 3, 'scorea' => 42, 'teamb_name' => 'ZEN', 'teamb_id' => 5, 'scoreb' => 60]),
        ]);
        $repo->method('getByeWeeks')->willReturn([]);

        $schedule = $this->makeService($repo, currentSeason: 2024, currentWeek: 5)->getSchedule(2024);
        $game = $schedule['weeks'][0]['games'][0];

        $this->assertSame('ZEN', $game['winName']);
        $this->assertSame(60, $game['winScore']);
        $this->assertSame('Norsemen', $game['loseName']);
        $this->assertTrue($game['showScores']);
    }

    public function testGetScheduleHidesScoresForCurrentOrFutureWeeks(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('getSeasonSchedule')->willReturn([
            $this->row(['week' => 5]),
        ]);
        $repo->method('getByeWeeks')->willReturn([]);

        $schedule = $this->makeService($repo, currentSeason: 2024, currentWeek: 5)->getSchedule(2024);

        $this->assertFalse($schedule['weeks'][0]['games'][0]['showScores']);
    }

    public function testGetScheduleTreatsEarlierSeasonsAsFullyPast(): void
    {
        $repo = $this->createStub(ScheduleRepository::class);
        $repo->method('getSeasonSchedule')->willReturn([
            $this->row(['week' => 16]),
        ]);
        $repo->method('getByeWeeks')->willReturn([]);

        // currentWeek is early in 2026, but 2024 is a fully-played past season
        $schedule = $this->makeService($repo, currentSeason: 2026, currentWeek: 2)->getSchedule(2024);

        $this->assertTrue($schedule['weeks'][0]['games'][0]['showScores']);
    }

    // ---- helpers ----

    private function row(array $overrides): array
    {
        return array_merge([
            'week' => 1,
            'teama_name' => 'Norsemen', 'teama_id' => 3, 'scorea' => 20,
            'teamb_name' => 'ZEN', 'teamb_id' => 5, 'scoreb' => 10,
            'weekname' => 'Week 1', 'displayDate' => '2024-09-08', 'endDate' => '2024-09-09',
            'label' => '', 'postseason' => 0,
        ], $overrides);
    }

    private function makeService(ScheduleRepository $repo, int $currentSeason, int $currentWeek = 0): ScheduleService
    {
        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn($currentSeason);
        $seasonWeek->method('getCurrentWeek')->willReturn($currentWeek);

        return new ScheduleService($repo, $seasonWeek);
    }
}
