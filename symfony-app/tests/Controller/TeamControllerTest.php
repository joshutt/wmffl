<?php

namespace App\Tests\Controller;

use App\Controller\TeamController;
use App\Repository\TeamRepository;
use App\Service\SeasonWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class TeamControllerTest extends TestCase
{
    // ---- GET /teams ----

    public function testIndexGroupsTeamsByDivisionSorted(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getTeamsByDivision')->willReturn([
            ['teamid' => 3, 'team' => 'Norsemen', 'division' => 'Burgundy Division', 'divisionid' => 1, 'owner' => 'Byron'],
            ['teamid' => 2, 'team' => 'Amish Electricians', 'division' => 'Gold Division', 'divisionid' => 2, 'owner' => 'Josh'],
            ['teamid' => 6, 'team' => 'Crusaders', 'division' => 'Gold Division', 'divisionid' => 2, 'owner' => 'Jon'],
        ]);

        $controller = $this->makeController($repo);
        $controller->index();

        $this->assertSame('team/index.html.twig', $controller->renderedView);
        $divisions = $controller->renderedParams['divisions'];
        $this->assertSame(['Burgundy Division', 'Gold Division'], array_keys($divisions));
        $this->assertCount(2, $divisions['Gold Division']);
    }

    public function testIndexQueriesTheCurrentSeason(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->expects($this->once())->method('getTeamsByDivision')->with(2026)->willReturn([]);

        $this->makeController($repo, season: 2026)->index();
    }

    // ---- GET /teams/squirrels ----

    public function testSquirrelsRendersTheStaticPage(): void
    {
        $controller = $this->makeController($this->createStub(TeamRepository::class));
        $controller->squirrels();

        $this->assertSame('team/squirrels.html.twig', $controller->renderedView);
    }

    // ---- GET /teams/compare ----

    public function testCompareWithoutSelectionRendersBareForm(): void
    {
        $teams = [['name' => 'Amish Electricians', 'teamid' => 2]];
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getActiveTeams')->willReturn($teams);
        $repo->expects($this->never())->method('getRostersForComparison');

        $controller = $this->makeController($repo);
        $controller->compare(new Request());

        $this->assertSame('team/compare.html.twig', $controller->renderedView);
        $this->assertSame($teams, $controller->renderedParams['teams']);
        $this->assertNull($controller->renderedParams['rosters']);
    }

    public function testCompareGroupsRostersByTeamName(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getActiveTeams')->willReturn([]);
        $repo->expects($this->once())->method('getRostersForComparison')->with(2, 4)->willReturn([
            ['name' => 'Steve Largent', 'playerid' => 7, 'pos' => 'WR', 'team' => 'SEA', 'teamname' => 'Amish Electricians'],
            ['name' => 'Jim Zorn', 'playerid' => 9, 'pos' => 'QB', 'team' => 'SEA', 'teamname' => 'Jew Thing'],
        ]);

        $controller = $this->makeController($repo);
        $controller->compare(new Request(query: ['teamone' => '2', 'teamtwo' => '4']));

        $rosters = $controller->renderedParams['rosters'];
        $this->assertSame(['Amish Electricians', 'Jew Thing'], array_keys($rosters));
        $this->assertSame(2, $controller->renderedParams['teamOne']);
        $this->assertSame(4, $controller->renderedParams['teamTwo']);
    }

    public function testCompareInvalidIdsRenderEmptyComparisonWithoutErrors(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getActiveTeams')->willReturn([]);
        $repo->method('getRostersForComparison')->willReturn([]);

        $controller = $this->makeController($repo);
        $controller->compare(new Request(query: ['teamone' => '998', 'teamtwo' => '999']));

        $this->assertSame([], $controller->renderedParams['rosters']);
    }

    // ---- GET /team/{id} ----

    public function testShowPermanentlyRedirectsToRoster(): void
    {
        $controller = $this->makeController($this->createStub(TeamRepository::class));

        $response = $controller->show(2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_roster', ['id' => 2]], $controller->redirectedTo);
    }

    // ---- GET /team/{id}/roster ----

    public function testRosterRendersHeaderSummaryAndRosterMidSeason(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->method('getChampionshipSeasons')->willReturn([1998]);
        $repo->expects($this->once())->method('getCurrentRoster')->with(2)->willReturn([['playerid' => 7]]);
        $repo->expects($this->once())->method('getTransactionSummary')->with(2, 2025)
            ->willReturn(['remaining' => 55, 'roster_count' => 26]);

        $controller = $this->makeController($repo, season: 2025, week: 8);
        $controller->roster(2);

        $this->assertSame('team/roster.html.twig', $controller->renderedView);
        $params = $controller->renderedParams;
        $this->assertSame('Roster', $params['page']);
        $this->assertSame([1998], $params['championships']);
        // mid-season: cost is next season's, points this season's
        $this->assertSame(2026, $params['costSeason']);
        $this->assertSame(2025, $params['ptsSeason']);
    }

    public function testRosterSeasonColumnsFlipAtTheSeasonBoundary(): void
    {
        foreach ([0, 1] as $week) {
            $repo = $this->createStub(TeamRepository::class);
            $repo->method('getTeamHeader')->willReturn($this->header(2));

            $controller = $this->makeController($repo, season: 2026, week: $week);
            $controller->roster(2);

            $this->assertSame(2026, $controller->renderedParams['costSeason']);
            $this->assertSame(2025, $controller->renderedParams['ptsSeason']);
        }
    }

    public function testRosterUnknownTeam404s(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->makeController($repo)->roster(999);
    }

    // ---- GET /team/{id}/schedule ----

    public function testScheduleDefaultsToCurrentSeasonAndWeek(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->expects($this->once())->method('getSeasonSchedule')->with(2, 2025)->willReturn([]);
        $repo->method('getSeasonsPlayed')->willReturn([2025, 2024]);
        $repo->method('getOpponentList')->willReturn([]);

        $controller = $this->makeController($repo, season: 2025, week: 8);
        $controller->schedule(2, null, new Request());

        $this->assertSame('team/schedule.html.twig', $controller->renderedView);
        $this->assertSame(2025, $controller->renderedParams['season']);
        $this->assertSame(8, $controller->renderedParams['checkWeek']);
        $this->assertSame([2025, 2024], $controller->renderedParams['seasons']);
    }

    public function testScheduleWeekZeroFallsBackToPriorSeasonFullyPlayed(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->expects($this->once())->method('getSeasonSchedule')->with(2, 2025)->willReturn([]);
        $repo->method('getSeasonsPlayed')->willReturn([]);
        $repo->method('getOpponentList')->willReturn([]);

        $controller = $this->makeController($repo, season: 2026, week: 0);
        $controller->schedule(2, null, new Request());

        $this->assertSame(2025, $controller->renderedParams['season']);
        $this->assertSame(17, $controller->renderedParams['checkWeek']);
    }

    public function testSchedulePastSeasonShowsEveryResult(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->expects($this->once())->method('getSeasonSchedule')->with(2, 2019)->willReturn([]);
        $repo->method('getSeasonsPlayed')->willReturn([]);
        $repo->method('getOpponentList')->willReturn([]);

        $controller = $this->makeController($repo, season: 2025, week: 8);
        $controller->schedule(2, 2019, new Request());

        $this->assertSame(2019, $controller->renderedParams['season']);
        $this->assertSame(17, $controller->renderedParams['checkWeek']);
    }

    public function testScheduleAcceptsSeasonAsQueryParamFromTheNoJsForm(): void
    {
        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->expects($this->once())->method('getSeasonSchedule')->with(2, 2019)->willReturn([]);
        $repo->method('getSeasonsPlayed')->willReturn([]);
        $repo->method('getOpponentList')->willReturn([]);

        $controller = $this->makeController($repo, season: 2025, week: 8);
        $controller->schedule(2, null, new Request(query: ['season' => '2019']));

        $this->assertSame(2019, $controller->renderedParams['season']);
    }

    public function testScheduleUnknownTeam404s(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->makeController($repo)->schedule(999, null, new Request());
    }

    // ---- head-to-head (?vs=) ----

    public function testVsParamRendersHeadToHeadWithRecordAndGames(): void
    {
        $games = [['season' => 1993, 'weekname' => 'Week 1', 'opponent' => 'Jew Thing', 'week' => 1, 'score' => 100, 'oppscore' => 90]];
        $record = ['win' => 37, 'loss' => 31, 'tie' => 1, 'pct' => '0.543'];

        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->method('getOpponentList')->willReturn([['name' => 'Jew Thing', 'teamid' => 4]]);
        $repo->expects($this->once())->method('getHeadToHead')->with(2, 4)->willReturn($games);
        $repo->expects($this->once())->method('getHeadToHeadRecord')->with(2, 4)->willReturn($record);

        $controller = $this->makeController($repo, season: 2025, week: 8);
        $controller->schedule(2, null, new Request(query: ['vs' => '4']));

        $this->assertSame('team/h2h.html.twig', $controller->renderedView);
        $params = $controller->renderedParams;
        $this->assertSame('Jew Thing', $params['opponentName']);
        $this->assertSame($games, $params['games']);
        $this->assertSame($record, $params['record']);
        $this->assertSame(2025, $params['currentSeason']);
        $this->assertSame(8, $params['checkWeek']);
    }

    public function testHeadToHeadWeekZeroTreatsCurrentSeasonAsComplete(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->method('getOpponentList')->willReturn([['name' => 'Jew Thing', 'teamid' => 4]]);
        $repo->method('getHeadToHeadRecord')->willReturn(['win' => 0, 'loss' => 0, 'tie' => 0, 'pct' => '0.000']);

        $controller = $this->makeController($repo, season: 2026, week: 0);
        $controller->schedule(2, null, new Request(query: ['vs' => '4']));

        $this->assertSame(17, $controller->renderedParams['checkWeek']);
    }

    public function testUnknownOpponent404s(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->method('getOpponentList')->willReturn([['name' => 'Jew Thing', 'teamid' => 4]]);

        $this->expectException(NotFoundHttpException::class);
        $this->makeController($repo)->schedule(2, null, new Request(query: ['vs' => '999']));
    }

    // ---- GET /team/{id}/history ----

    public function testHistoryAssemblesAllTimePlayoffAndSeasonRecords(): void
    {
        $seasonRecords = [
            ['label' => '2025', 'win' => 9, 'lose' => 4, 'tie' => 1, 'pct' => '0.679'],
            ['label' => '2024', 'win' => 5, 'lose' => 9, 'tie' => 0, 'pct' => '0.357'],
        ];
        $playoffRecords = [['label' => 'Playoffs', 'win' => 10, 'lose' => 8, 'tie' => 0, 'pct' => '0.556']];

        $repo = $this->createMock(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn($this->header(2));
        $repo->expects($this->once())->method('getRegularSeasonRecords')->with(2, 8, 2025)->willReturn($seasonRecords);
        $repo->method('getPlayoffRecord')->willReturn($playoffRecords);
        $repo->method('getPlayoffResults')->willReturn([]);
        $repo->method('getTitles')->willReturn(['league' => [], 'division' => []]);
        $repo->method('getPastOwners')->willReturn([]);
        $repo->method('getPastNames')->willReturn([]);

        $controller = $this->makeController($repo, season: 2025, week: 8);
        $controller->history(2);

        $this->assertSame('team/history.html.twig', $controller->renderedView);
        $records = $controller->renderedParams['records'];
        // All-Time first (bold row), then playoffs, then per-season desc
        $this->assertSame(['All-Time', 'Playoffs', '2025', '2024'], array_column($records, 'label'));
        $this->assertSame(14, $records[0]['win']);
        $this->assertSame('0.518', $records[0]['pct']);
    }

    public function testHistoryUnknownTeam404s(): void
    {
        $repo = $this->createStub(TeamRepository::class);
        $repo->method('getTeamHeader')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->makeController($repo)->history(999);
    }

    // ---- helpers ----

    private function makeController(TeamRepository $repo, int $season = 2026, int $week = 0): TeamController
    {
        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn($season);
        $seasonWeek->method('getCurrentWeek')->willReturn($week);

        return new class($repo, $seasonWeek) extends TeamController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }
        };
    }

    private function header(int $id): array
    {
        return [
            'teamid' => $id, 'name' => 'Amish Electricians', 'member' => 1992,
            'motto' => null, 'logo' => 'ae.jpg', 'fulllogo' => false,
            'owners' => 'Josh Utterback', 'owner_count' => 1, 'owner_since' => '1992',
        ];
    }
}
