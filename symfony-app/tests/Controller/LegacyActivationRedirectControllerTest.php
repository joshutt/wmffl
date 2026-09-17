<?php

namespace App\Tests\Controller;

use App\Controller\LegacyActivationRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LegacyActivationRedirectControllerTest extends TestCase
{
    public function testTheLineupViewRedirectsBare(): void
    {
        $controller = $this->makeController();
        $response = $controller->activations(new Request());

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['activations', []], $controller->redirectedTo);
    }

    public function testTheLineupViewCarriesSeasonAndWeek(): void
    {
        $controller = $this->makeController();
        $controller->activations(new Request(query: ['season' => '2019', 'week' => '7']));

        $this->assertSame(['activations', ['season' => 2019, 'week' => 7]], $controller->redirectedTo);
    }

    public function testTheSubmitFormCarriesTheWeek(): void
    {
        $controller = $this->makeController();
        $response = $controller->submit(new Request(query: ['week' => '12']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['activations_submit', ['week' => 12]], $controller->redirectedTo);
    }

    public function testTheSubmitFormRedirectsBareWithoutAWeek(): void
    {
        $controller = $this->makeController();
        $controller->submit(new Request());

        $this->assertSame(['activations_submit', []], $controller->redirectedTo);
    }

    /** The old save handler and its orphaned thank-you page */
    public function testTheRetiredSaveHandlerLandsOnTheNewForm(): void
    {
        $controller = $this->makeController();
        $response = $controller->process();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['activations_submit', []], $controller->redirectedTo);
    }

    public function testTheDeletedPhpinfoPageLandsOnTheLineupView(): void
    {
        $controller = $this->makeController();
        $response = $controller->info();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['activations', []], $controller->redirectedTo);
    }

    private function makeController(): LegacyActivationRedirectController
    {
        return new class extends LegacyActivationRedirectController {
            public ?array $redirectedTo = null;

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];

                return new RedirectResponse('/stub', $status);
            }
        };
    }
}
