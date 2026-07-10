<?php

namespace App\Tests\Controller;

use App\Controller\LegacyTeamRedirectController;
use App\Repository\TeamRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

#[AllowMockObjectsWithoutExpectations]
class LegacyTeamRedirectControllerTest extends TestCase
{
    public function testRosterRedirectResolvesNumericId(): void
    {
        $controller = $this->makeController('2', 2);

        $response = $controller->roster(new Request(query: ['viewteam' => '2']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_roster', ['id' => 2]], $controller->redirectedTo);
    }

    public function testRosterRedirectResolvesAbbreviation(): void
    {
        $controller = $this->makeController('AE', 2);

        $controller->roster(new Request(query: ['viewteam' => 'AE']));

        $this->assertSame(['team_roster', ['id' => 2]], $controller->redirectedTo);
    }

    public function testRosterRedirectResolvesTeamName(): void
    {
        $controller = $this->makeController('Amish Electricians', 2);

        $controller->roster(new Request(query: ['viewteam' => 'Amish Electricians']));

        $this->assertSame(['team_roster', ['id' => 2]], $controller->redirectedTo);
    }

    public function testMissingViewteamLandsOnTheTeamsIndex(): void
    {
        $controller = $this->makeController(null, null);

        $response = $controller->roster(new Request());

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_index', []], $controller->redirectedTo);
    }

    public function testUnresolvableViewteamLandsOnTheTeamsIndex(): void
    {
        $controller = $this->makeController('bogus', null);

        $controller->roster(new Request(query: ['viewteam' => 'bogus']));

        $this->assertSame(['team_index', []], $controller->redirectedTo);
    }

    public function testScheduleRedirectCarriesVsTeamIntoTheH2hView(): void
    {
        $controller = $this->makeController('2', 2);

        $response = $controller->schedule(new Request(query: ['viewteam' => '2', 'vsTeam' => '4']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_schedule', ['id' => 2, 'vs' => 4]], $controller->redirectedTo);
    }

    public function testScheduleRedirectWithoutVsTeam(): void
    {
        $controller = $this->makeController('2', 2);

        $controller->schedule(new Request(query: ['viewteam' => '2']));

        $this->assertSame(['team_schedule', ['id' => 2]], $controller->redirectedTo);
    }

    public function testHistoryRedirect(): void
    {
        $controller = $this->makeController('NOR', 3);

        $response = $controller->history(new Request(query: ['viewteam' => 'NOR']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_history', ['id' => 3]], $controller->redirectedTo);
    }

    public function testCompareRedirectCarriesGetParams(): void
    {
        $controller = $this->makeController(null, null);

        $response = $controller->compare(new Request(query: ['teamone' => '2', 'teamtwo' => '4']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_compare', ['teamone' => 2, 'teamtwo' => 4]], $controller->redirectedTo);
    }

    public function testCompareRedirectCarriesLegacyPostedParams(): void
    {
        $controller = $this->makeController(null, null);

        $controller->compare(new Request(request: ['teamone' => '2', 'teamtwo' => '4']));

        $this->assertSame(['team_compare', ['teamone' => 2, 'teamtwo' => 4]], $controller->redirectedTo);
    }

    public function testSquirrelsPhpAliasRedirectsToTheNewPage(): void
    {
        $controller = $this->makeController(null, null);

        $response = $controller->squirrels();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['team_squirrels', []], $controller->redirectedTo);
    }

    public function testCompareRedirectWithoutParamsLandsOnTheBareForm(): void
    {
        $controller = $this->makeController(null, null);

        $controller->compare(new Request());

        $this->assertSame(['team_compare', []], $controller->redirectedTo);
    }

    // ---- helpers ----

    private function makeController(?string $expectedViewteam, ?int $resolvedId): LegacyTeamRedirectController
    {
        $repo = $this->createMock(TeamRepository::class);
        if ($expectedViewteam === null) {
            $repo->method('resolveTeamId')->willReturn($resolvedId);
        } else {
            $repo->expects($this->once())->method('resolveTeamId')
                ->with($expectedViewteam)->willReturn($resolvedId);
        }

        return new class($repo) extends LegacyTeamRedirectController {
            public ?array $redirectedTo = null;

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }
        };
    }
}
