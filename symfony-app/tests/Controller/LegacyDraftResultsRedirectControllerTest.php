<?php

namespace App\Tests\Controller;

use App\Controller\LegacyDraftResultsRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LegacyDraftResultsRedirectControllerTest extends TestCase
{
    public function testRedirectsToTheYearDraftResultsRoute(): void
    {
        $controller = $this->makeController();
        $response = $controller->year(2019);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['transactions_draft_results', ['year' => 2019]], $controller->redirectedTo);
    }

    public function testEarliestYearRedirectsTheSameWay(): void
    {
        $controller = $this->makeController();
        $controller->year(2007);

        $this->assertSame(['transactions_draft_results', ['year' => 2007]], $controller->redirectedTo);
    }

    public function testCurrentSeasonYearRedirects(): void
    {
        $controller = $this->makeController();
        $controller->year(2025);

        $this->assertSame(['transactions_draft_results', ['year' => 2025]], $controller->redirectedTo);
    }

    private function makeController(): LegacyDraftResultsRedirectController
    {
        return new class extends LegacyDraftResultsRedirectController {
            public ?array $redirectedTo = null;

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }
        };
    }
}
