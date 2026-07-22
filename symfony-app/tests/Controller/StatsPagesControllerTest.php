<?php

namespace App\Tests\Controller;

use App\Controller\StatsController;
use App\Repository\StatsRepository;
use App\Service\AuthenticationService;
use App\Service\InjuryReportService;
use App\Service\LuckService;
use App\Service\PlayerRecordsService;
use App\Service\PowerRatingService;
use App\Service\SeasonRuleService;
use App\Service\SeasonWeekService;
use App\Service\WeekByWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Covers the weekbyweek/power/luck/records/injuries actions added on
 * top of the core StatsControllerTest.
 */
#[AllowMockObjectsWithoutExpectations]
class StatsPagesControllerTest extends TestCase
{
    private const GRID = ['titles' => ['Name', 'Pos', 'NFL', 'Team', 1, 'Tot'], 'rows' => [['A', 'QB', 'SEA', 'AE', 5, 5]]];

    // ---- /stats/weekbyweek ----

    public function testWeekByWeekExplicitTeamWins(): void
    {
        $weekByWeek = $this->createMock(WeekByWeekService::class);
        $weekByWeek->expects($this->once())->method('getTeamGrid')->with(2025, 4)->willReturn(self::GRID);
        $weekByWeek->method('getTeamList')->willReturn([]);

        $controller = $this->makeController(season: 2025, week: 9);
        $controller->weekByWeek(new Request(query: ['team' => '4']), $weekByWeek, $this->auth(null));

        $this->assertSame('stats/weekbyweek.html.twig', $controller->renderedView);
        $this->assertSame(4, $controller->renderedParams['selectedTeam']);
    }

    public function testWeekByWeekPositionModeUsesThePositionGrid(): void
    {
        $weekByWeek = $this->createMock(WeekByWeekService::class);
        $weekByWeek->expects($this->once())->method('getPositionGrid')->with(2025, 'WR')->willReturn(self::GRID);
        $weekByWeek->method('getTeamList')->willReturn([]);

        $controller = $this->makeController(season: 2025, week: 9);
        $controller->weekByWeek(new Request(query: ['pos' => 'WR']), $weekByWeek, $this->auth(null));

        $this->assertSame('WR', $controller->renderedParams['selectedPos']);
    }

    public function testWeekByWeekDefaultsToTheMembersTeamThenTeamOne(): void
    {
        $weekByWeek = $this->createMock(WeekByWeekService::class);
        $weekByWeek->expects($this->exactly(2))->method('getTeamGrid')
            ->willReturnCallback(function (int $season, int $team) {
                static $expected = [7, 1];
                TestCase::assertSame(array_shift($expected), $team);
                return self::GRID;
            });
        $weekByWeek->method('getTeamList')->willReturn([]);

        $controller = $this->makeController(season: 2025, week: 9);
        $controller->weekByWeek(new Request(), $weekByWeek, $this->auth(7));
        $controller->weekByWeek(new Request(), $weekByWeek, $this->auth(null));
    }

    public function testWeekByWeekCsvFormatDownloads(): void
    {
        $weekByWeek = $this->createStub(WeekByWeekService::class);
        $weekByWeek->method('getTeamGrid')->willReturn(self::GRID);
        $weekByWeek->method('getTeamList')->willReturn([]);

        $response = $this->makeController(season: 2025, week: 9)
            ->weekByWeek(new Request(request: ['team' => '4', 'format' => 'csv']), $weekByWeek, $this->auth(null));

        $this->assertStringContainsString('weekbyweek.csv', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('A,QB,SEA,AE,5,5', $response->getContent());
    }

    // ---- /stats/power + /stats/luck ----

    public function testPowerRendersRatingsAndLines(): void
    {
        $ratings = ['B' => [1 => 50.0], 'A' => [1 => 40.0]];
        $power = $this->createMock(PowerRatingService::class);
        $power->expects($this->once())->method('getPowerRatings')->with(2025)
            ->willReturn(['ratings' => $ratings, 'week' => 1]);
        $power->expects($this->once())->method('getLines')->with(2025, 1, $ratings)
            ->willReturn([['favorite' => 'B', 'underdog' => 'A', 'line' => 5.0]]);

        $controller = $this->makeController(season: 2026, week: 0); // offseason -> 2025
        $controller->power($power);

        $this->assertSame('stats/power.html.twig', $controller->renderedView);
        $this->assertSame($ratings, $controller->renderedParams['ratings']);
        $this->assertSame(1, $controller->renderedParams['week']);
    }

    public function testLuckRendersTheServiceResult(): void
    {
        $luck = $this->createMock(LuckService::class);
        $luck->expects($this->once())->method('getLuckRatings')->with(2025)
            ->willReturn(['luck' => ['A' => 12.5], 'week' => 14, 'statSig' => 7.1]);

        $controller = $this->makeController(season: 2025, week: 14);
        $controller->luck($luck);

        $this->assertSame('stats/luck.html.twig', $controller->renderedView);
        $this->assertSame(['A' => 12.5], $controller->renderedParams['luck']);
    }

    // ---- /stats/records + /stats/lastplayer + /stats/injuries ----

    public function testRecordsUsesTheDefaultSeason(): void
    {
        $records = $this->createMock(PlayerRecordsService::class);
        $records->expects($this->once())->method('getRecords')->with(2025)
            ->willReturn(['game' => [], 'season' => []]);

        $controller = $this->makeController(season: 2026, week: 0, maxWeek: 16);
        $controller->records($records);

        $this->assertSame('stats/records.html.twig', $controller->renderedView);
        $this->assertSame(16, $controller->renderedParams['week']);
    }

    public function testLastPlayerServesTheFrozen2005Snapshot(): void
    {
        $records = $this->createMock(PlayerRecordsService::class);
        $records->expects($this->once())->method('getLastPlayerRecords')
            ->willReturn(['game' => [], 'season' => []]);

        $controller = $this->makeController();
        $controller->lastPlayer($records);

        $this->assertSame('stats/records.html.twig', $controller->renderedView);
    }

    public function testInjuriesLoadsAllThreeSections(): void
    {
        $injuries = $this->createMock(InjuryReportService::class);
        $injuries->expects($this->once())->method('getCurrentIrLists')->willReturn(['ir' => [], 'covid' => []]);
        $injuries->expects($this->once())->method('getEligible')->willReturn([]);
        $injuries->expects($this->once())->method('getFullReport')->willReturn([]);

        $controller = $this->makeController();
        $controller->injuries($injuries);

        $this->assertSame('stats/injuries.html.twig', $controller->renderedView);
    }

    // ---- helpers ----

    private function auth(?int $teamNum): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        return $auth;
    }

    private function makeController(int $season = 2026, int $week = 0, int $maxWeek = 14): StatsController
    {
        $repo = $this->createStub(StatsRepository::class);
        $repo->method('getMaxScoredWeek')->willReturn($maxWeek);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn($season);
        $seasonWeek->method('getCurrentWeek')->willReturn($week);

        $seasonRules = $this->createStub(SeasonRuleService::class);
        $seasonRules->method('getRegularSeasonWeeks')->willReturn(14);

        return new class($repo, $seasonWeek, $seasonRules) extends StatsController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }
        };
    }
}
