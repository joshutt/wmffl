<?php

namespace App\Tests\Controller;

use App\Controller\StatsController;
use App\Repository\StatsRepository;
use App\Service\SeasonRuleService;
use App\Service\SeasonWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class StatsControllerTest extends TestCase
{
    // ---- GET /stats/leaders ----

    public function testLeadersDefaultsToCurrentSeasonMidSeason(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getLeaders')->with(2025)->willReturn([$this->leaderRow()]);
        $repo->method('getMaxScoredWeek')->willReturn(9);

        $controller = $this->makeController($repo, season: 2025, week: 9);
        $controller->leaders(new Request());

        $this->assertSame('stats/leaders.html.twig', $controller->renderedView);
        $this->assertSame(9, $controller->renderedParams['week']);
        $this->assertSame('Team', $controller->renderedParams['titles'][0]);
        $this->assertSame('Total Pts', end($controller->renderedParams['titles']));
        $this->assertSame('Amish Electricians', $controller->renderedParams['rows'][0][0]);
    }

    public function testLeadersOffseasonFallsBackToTheCompletedSeason(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getLeaders')->with(2025)->willReturn([]);
        $repo->method('getMaxScoredWeek')->willReturn(14);

        $this->makeController($repo, season: 2026, week: 0)->leaders(new Request());
    }

    public function testLeadersHonorsAnExplicitSeason(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getLeaders')->with(2019)->willReturn([]);
        $repo->method('getMaxScoredWeek')->willReturn(14);

        $this->makeController($repo)->leaders(new Request(query: ['season' => '2019']));
    }

    public function testLeadersCsvFormatDownloadsCsv(): void
    {
        $repo = $this->createStub(StatsRepository::class);
        $repo->method('getLeaders')->willReturn([$this->leaderRow()]);

        $response = $this->makeController($repo)->leaders(new Request(request: ['format' => 'csv']));

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('leaders.csv', $response->headers->get('Content-Disposition'));
        $lines = explode("\n", trim($response->getContent()));
        $this->assertSame('Team,HC,QB,RB,WR,TE,K,OL,DL,LB,DB,Offense,Defense,"Total Pts"', $lines[0]);
        $this->assertStringStartsWith('"Amish Electricians",12,140', $lines[1]);
        $this->assertCount(2, $lines);
    }

    public function testLeadersJsonFormatKeysRowsByTitle(): void
    {
        $repo = $this->createStub(StatsRepository::class);
        $repo->method('getLeaders')->willReturn([$this->leaderRow()]);

        $response = $this->makeController($repo)->leaders(new Request(query: ['format' => 'json']));

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Amish Electricians', $data[0]['Team']);
        $this->assertSame(695, $data[0]['Total Pts']);
    }

    public function testLeadersAjaxFormatRendersOnlyTheTable(): void
    {
        $repo = $this->createStub(StatsRepository::class);
        $repo->method('getLeaders')->willReturn([]);

        $controller = $this->makeController($repo);
        $controller->leaders(new Request(request: ['format' => 'ajax']));

        $this->assertSame('stats/_table.html.twig', $controller->renderedView);
    }

    // ---- GET /stats/players ----

    public function testPlayersBuildsRowsInLegacyColumnOrder(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getPlayerStats')
            ->with(2025, 'RB', 'ppg', 1, 17, true)
            ->willReturn([[
                'playerid' => 7, 'name' => 'Bo Jackson', 'pos' => 'RB', 'team' => 'LV', 'bye' => 6,
                'ffteam' => 'AE', 'games' => 10, 'pts' => 150, 'ppg' => 15.0,
                'yards' => 900, 'rec' => 20, 'tds' => 9, 'fum' => 2, 'twopt' => 1, 'spectd' => 0,
            ]]);

        $controller = $this->makeController($repo, season: 2025, week: 9);
        $controller->players(new Request(query: ['pos' => 'RB']));

        $this->assertSame('stats/players.html.twig', $controller->renderedView);
        $this->assertSame(
            ['Name', 'NFL Team', 'Bye', 'FF Team', 'G', 'Pts', 'PPG', 'Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'],
            $controller->renderedParams['titles']
        );
        $this->assertSame(
            ['Bo Jackson', 'LV', 6, 'AE', 10, 150, 15.0, 900, 20, 9, 2, 1, 0],
            $controller->renderedParams['rows'][0]
        );
        $this->assertSame('RB', $controller->renderedParams['pos']);
    }

    public function testPlayersUnknownPositionFallsBackToQb(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getPlayerStats')
            ->with(2025, 'QB', 'ppg', 1, 17, true)->willReturn([]);

        $controller = $this->makeController($repo, season: 2025, week: 9);
        $controller->players(new Request(query: ['pos' => "QB'; DROP TABLE stats--"]));

        $this->assertSame('QB', $controller->renderedParams['pos']);
    }

    public function testPlayersCsvOmitsTheHcPenaltyColumn(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getPlayerStats')
            ->with(2025, 'HC', 'ppg', 2, 10, false)
            ->willReturn([[
                'playerid' => 7, 'name' => 'Chuck Knox', 'pos' => 'HC', 'team' => 'SEA', 'bye' => 6,
                'ffteam' => 'AE', 'games' => 9, 'pts' => 60, 'ppg' => 6.67,
                'wins' => 6, 'ptdiff' => 55,
            ]]);

        $response = $this->makeController($repo, season: 2025, week: 9)->players(new Request(query: [
            'pos' => 'HC', 'format' => 'csv', 'startWeek' => '2', 'endWeek' => '10',
        ]));

        $lines = explode("\n", trim($response->getContent()));
        $this->assertSame('Name,"NFL Team",Bye,"FF Team",G,Pts,PPG,Wins,"Pt Diff"', $lines[0]);
        $this->assertSame('"Chuck Knox",SEA,6,AE,9,60,6.67,6,55', $lines[1]);
    }

    // ---- GET /stats/playerlist ----

    public function testPlayerlistEmitsThePlainTextFeed(): void
    {
        $repo = $this->createMock(StatsRepository::class);
        $repo->expects($this->once())->method('getActivePlayerScores')->with(2026)->willReturn([
            ['lastname' => 'Largent', 'firstname' => 'Steve', 'pos' => 'WR', 'team' => 'SEA', 'week' => 1, 'pts' => 21],
        ]);

        $response = $this->makeController($repo, season: 2026, week: 5)->playerlist();

        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $this->assertSame(
            "Last Name,First Name,Pos,NFL,Week,Pts\nLargent,Steve,WR,SEA,1,21\n",
            $response->getContent()
        );
    }

    // ---- helpers ----

    private function leaderRow(): array
    {
        return [
            'name' => 'Amish Electricians',
            'HC' => 12, 'QB' => 140, 'RB' => 120, 'WR' => 90, 'TE' => 40, 'K' => 55, 'OL' => 48,
            'DL' => 60, 'LB' => 70, 'DB' => 60,
            'offense' => 505, 'defense' => 190, 'total' => 695,
        ];
    }

    private function makeController(StatsRepository $repo, int $season = 2026, int $week = 0): StatsController
    {
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
