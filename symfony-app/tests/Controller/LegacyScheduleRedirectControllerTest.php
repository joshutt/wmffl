<?php

namespace App\Tests\Controller;

use App\Controller\LegacyScheduleRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LegacyScheduleRedirectControllerTest extends TestCase
{
    public function testRedirectsToTheSeasonScheduleRoute(): void
    {
        $controller = $this->makeController();
        $response = $controller->year(2001);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['schedule', ['season' => 2001]], $controller->redirectedTo);
    }

    public function test1990sYearRedirectsTheSameWay(): void
    {
        $controller = $this->makeController();
        $controller->year(1994);

        $this->assertSame(['schedule', ['season' => 1994]], $controller->redirectedTo);
    }

    public function testCurrentSeasonYearRedirects(): void
    {
        $controller = $this->makeController();
        $controller->year(2025);

        $this->assertSame(['schedule', ['season' => 2025]], $controller->redirectedTo);
    }

    private function makeController(): LegacyScheduleRedirectController
    {
        return new class extends LegacyScheduleRedirectController {
            public ?array $redirectedTo = null;

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }
        };
    }
}
