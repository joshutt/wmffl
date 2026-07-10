<?php

namespace App\Tests\Controller;

use App\Controller\TransactionController;
use App\Repository\TransactionRepository;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use App\Service\TransactionHistoryService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class TransactionControllerTest extends TestCase
{
    // ---- GET /transactions ----

    public function testHistoryDefaultsToTheMonthOfTheLatestTransaction(): void
    {
        $repo = $this->createStub(TransactionRepository::class);
        $repo->method('getLastTransactionDate')->willReturn(
            ['lastupdate' => '10/6/2025', 'month' => 10, 'year' => 2025]
        );
        $historyService = $this->createMock(TransactionHistoryService::class);
        $historyService->expects($this->once())->method('buildHistory')->with(2025, 10)->willReturn([]);

        $controller = $this->makeController($repo);
        $controller->history(new Request(), $historyService);

        $this->assertSame('transactions/history.html.twig', $controller->renderedView);
        $this->assertSame('10/6/2025', $controller->renderedParams['lastUpdate']);
        // October: previous is September, next is November
        $this->assertSame(['year' => 2025, 'month' => 9], $controller->renderedParams['previous']);
        $this->assertSame(['year' => 2025, 'month' => 11], $controller->renderedParams['next']);
    }

    public function testHistoryAcceptsExplicitMonthAndYear(): void
    {
        $repo = $this->createStub(TransactionRepository::class);
        $repo->method('getLastTransactionDate')->willReturn(
            ['lastupdate' => '10/6/2025', 'month' => 10, 'year' => 2025]
        );
        $historyService = $this->createMock(TransactionHistoryService::class);
        $historyService->expects($this->once())->method('buildHistory')->with(2019, 12)->willReturn([]);

        $controller = $this->makeController($repo);
        $controller->history(new Request(query: ['year' => '2019', 'month' => '12']), $historyService);

        // December: next wraps into the following offseason block
        $this->assertSame(['year' => 2019, 'month' => 11], $controller->renderedParams['previous']);
        $this->assertSame(['year' => 2020, 'month' => 8], $controller->renderedParams['next']);
    }

    public function testHistoryOffseasonMonthNavigatesAcrossSeasons(): void
    {
        $repo = $this->createStub(TransactionRepository::class);
        $repo->method('getLastTransactionDate')->willReturn(
            ['lastupdate' => '10/6/2025', 'month' => 10, 'year' => 2025]
        );
        $historyService = $this->createStub(TransactionHistoryService::class);
        $historyService->method('buildHistory')->willReturn([]);

        $controller = $this->makeController($repo);
        // Any month 1-8 shows the Jan-Aug offseason block
        $controller->history(new Request(query: ['year' => '2020', 'month' => '3']), $historyService);

        $this->assertSame(['year' => 2019, 'month' => 12], $controller->renderedParams['previous']);
        $this->assertSame(['year' => 2020, 'month' => 4], $controller->renderedParams['next']);
    }

    // ---- GET /transactions/waivers ----

    public function testWaiversShowsOrderAndAwardsToAnonymousVisitors(): void
    {
        $repo = $this->createMock(TransactionRepository::class);
        $repo->expects($this->once())->method('getWaiverOrder')->with(2025, 5)
            ->willReturn(['Norsemen', 'Amish Electricians']);
        $repo->expects($this->once())->method('getWaiverAwards')->with(2025, 5)->willReturn([]);
        $repo->expects($this->never())->method('getMemberWaiverPicks');

        $controller = $this->makeController($repo, season: 2025, week: 5, loggedIn: false);
        $controller->waivers();

        $this->assertSame('transactions/waivers.html.twig', $controller->renderedView);
        $this->assertSame(['Norsemen', 'Amish Electricians'], $controller->renderedParams['order']);
        $this->assertNull($controller->renderedParams['memberPicks']);
    }

    public function testWaiversIncludesMemberPicksWhenLoggedIn(): void
    {
        $picks = [['firstname' => 'Al', 'lastname' => 'Kaline', 'pos' => 'WR', 'team' => 'DET']];
        $repo = $this->createMock(TransactionRepository::class);
        $repo->method('getWaiverOrder')->willReturn([]);
        $repo->method('getWaiverAwards')->willReturn([]);
        $repo->expects($this->once())->method('getMemberWaiverPicks')->with(2025, 5, 2)->willReturn($picks);

        $controller = $this->makeController($repo, season: 2025, week: 5, loggedIn: true, teamNum: 2);
        $controller->waivers();

        $this->assertSame($picks, $controller->renderedParams['memberPicks']);
    }

    // ---- GET /transactions/protections/show ----

    public function testShowProtectionsGroupsByTeamByDefault(): void
    {
        $repo = $this->createMock(TransactionRepository::class);
        $repo->expects($this->once())->method('getProtections')->with(2026, true)->willReturn([
            ['name' => 'Amish Electricians', 'abbrev' => 'AE', 'player' => 'Al Kaline', 'pos' => 'WR', 'nflteamid' => 'DET', 'cost' => 3],
            ['name' => 'Amish Electricians', 'abbrev' => 'AE', 'player' => 'Bo Jackson', 'pos' => 'RB', 'nflteamid' => 'LV', 'cost' => 5],
            ['name' => 'Norsemen', 'abbrev' => 'NOR', 'player' => 'Cy Young', 'pos' => 'QB', 'nflteamid' => 'CLE', 'cost' => 4],
        ]);

        $controller = $this->makeController($repo, season: 2026);
        $controller->showProtections(new Request());

        $this->assertSame('transactions/protections_show.html.twig', $controller->renderedView);
        $groups = $controller->renderedParams['groups'];
        $this->assertSame(['Amish Electricians', 'Norsemen'], array_keys($groups));
        $this->assertCount(2, $groups['Amish Electricians']);
        // grouped by team, the detail column is the position
        $this->assertSame('WR', $groups['Amish Electricians'][0]['detail']);
        $this->assertSame('Pos', $controller->renderedParams['detailLabel']);
    }

    public function testShowProtectionsGroupsByPositionWithTeamAbbrevDetail(): void
    {
        $repo = $this->createMock(TransactionRepository::class);
        $repo->expects($this->once())->method('getProtections')->with(2019, false)->willReturn([
            ['name' => 'Norsemen', 'abbrev' => 'NOR', 'player' => 'Cy Young', 'pos' => 'QB', 'nflteamid' => 'CLE', 'cost' => 4],
            ['name' => 'Amish Electricians', 'abbrev' => 'AE', 'player' => 'Bo Jackson', 'pos' => 'RB', 'nflteamid' => 'LV', 'cost' => 5],
        ]);

        $controller = $this->makeController($repo);
        $controller->showProtections(new Request(query: ['order' => 'pos', 'season' => '2019']));

        $groups = $controller->renderedParams['groups'];
        $this->assertSame(['QB', 'RB'], array_keys($groups));
        $this->assertSame('NOR', $groups['QB'][0]['detail']);
        $this->assertSame('Team', $controller->renderedParams['detailLabel']);
        $this->assertSame(2019, $controller->renderedParams['season']);
    }

    // ---- helpers ----

    private function makeController(
        TransactionRepository $repo,
        int $season = 2026,
        int $week = 0,
        bool $loggedIn = false,
        ?int $teamNum = null
    ): TransactionController {
        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn($season);
        $seasonWeek->method('getCurrentWeek')->willReturn($week);
        $seasonWeek->method('getWeekName')->willReturn('Week ' . $week);

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        return new class($repo, $seasonWeek, $auth) extends TransactionController {
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
