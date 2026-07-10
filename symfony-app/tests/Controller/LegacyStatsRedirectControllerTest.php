<?php

namespace App\Tests\Controller;

use App\Controller\LegacyStatsRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LegacyStatsRedirectControllerTest extends TestCase
{
    public function testLeadersCarriesTheHistoryPageSeasonDeepLink(): void
    {
        $controller = $this->makeController();
        $response = $controller->leaders(new Request(query: ['season' => '2024']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['stats_leaders', ['season' => '2024']], $controller->redirectedTo);
    }

    public function testPlayerstatsCarriesPosSortSeason(): void
    {
        $controller = $this->makeController();
        $controller->playerstats(new Request(query: ['pos' => 'RB', 'sort' => 'pts', 'season' => '2023']));

        $this->assertSame(['stats_players', ['pos' => 'RB', 'sort' => 'pts', 'season' => '2023']], $controller->redirectedTo);
    }

    public function testStatcsvBecomesThePlayersCsvFormat(): void
    {
        $controller = $this->makeController();
        $controller->statcsv(new Request(query: ['pos' => 'K', 'startWeek' => '2', 'endWeek' => '9']));

        $this->assertSame(
            ['stats_players', ['pos' => 'K', 'startWeek' => '2', 'endWeek' => '9', 'format' => 'csv']],
            $controller->redirectedTo
        );
    }

    public function testWeekbyweekCarriesPostedSelections(): void
    {
        $controller = $this->makeController();
        $controller->weekbyweek(Request::create('/stats/weekbyweek.php', 'POST', ['team' => '4', 'format' => 'ajax']));

        $this->assertSame(['stats_weekbyweek', ['team' => '4', 'format' => 'ajax']], $controller->redirectedTo);
    }

    public function testDeadPagesLandOnTheStatsIndex(): void
    {
        $controller = $this->makeController();
        $response = $controller->deadPages();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['stats_index', []], $controller->redirectedTo);
    }

    public function testSimpleRenames(): void
    {
        $controller = $this->makeController();

        $controller->power();
        $this->assertSame(['stats_power', []], $controller->redirectedTo);

        $controller->injuryReport();
        $this->assertSame(['stats_injuries', []], $controller->redirectedTo);

        $controller->playerrecord();
        $this->assertSame(['stats_records', []], $controller->redirectedTo);

        $controller->lastplayer();
        $this->assertSame(['stats_lastplayer', []], $controller->redirectedTo);

        $controller->playerlist();
        $this->assertSame(['stats_playerlist', []], $controller->redirectedTo);
    }

    private function makeController(): LegacyStatsRedirectController
    {
        return new class extends LegacyStatsRedirectController {
            public ?array $redirectedTo = null;

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }
        };
    }
}
