<?php

namespace App\Tests\Controller;

use App\Controller\LegacyTransactionRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LegacyTransactionRedirectControllerTest extends TestCase
{
    public function testTransactionsCarriesMonthAndYear(): void
    {
        $controller = $this->makeController();
        $response = $controller->transactions(new Request(query: ['year' => '2024', 'month' => '11']));

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['transactions_history', ['year' => 2024, 'month' => 11]], $controller->redirectedTo);
    }

    public function testTransactionsWithoutParamsRedirectsBare(): void
    {
        $controller = $this->makeController();
        $controller->transactions(new Request());

        $this->assertSame(['transactions_history', []], $controller->redirectedTo);
    }

    public function testWaiverOrderRedirects(): void
    {
        $controller = $this->makeController();
        $response = $controller->waivers();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['transactions_waivers', []], $controller->redirectedTo);
    }

    public function testShowProtectionsCarriesSeasonAndNormalizesOrder(): void
    {
        $controller = $this->makeController();
        $controller->showProtections(new Request(query: ['season' => '2019', 'order' => 'pos']));

        $this->assertSame(['transactions_protections_show', ['season' => 2019, 'order' => 'pos']], $controller->redirectedTo);

        $controller->showProtections(new Request(query: ['order' => "team' OR 1=1"]));
        $this->assertSame(['transactions_protections_show', ['order' => 'team']], $controller->redirectedTo);
    }

    public function testDeadEndpointsLandOnSensiblePages(): void
    {
        $controller = $this->makeController();

        $controller->confirm();
        $this->assertSame(['transactions_list', []], $controller->redirectedTo);

        $controller->updateIr();
        $this->assertSame(['transactions_ir', []], $controller->redirectedTo);

        $controller->saveProtections();
        $this->assertSame(['transactions_protections', []], $controller->redirectedTo);

        $controller->deletedSubdirectories();
        $this->assertSame(['transactions_history', []], $controller->redirectedTo);
    }

    public function testRetiredTradesUrlsLandOnTheTradeScreen(): void
    {
        $controller = $this->makeController();
        $response = $controller->trades();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['trades_screen', []], $controller->redirectedTo);
    }

    public function testTransmenuLandsOnTheTransactionsHub(): void
    {
        $controller = $this->makeController();
        $response = $controller->transmenu();

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['transactions_history', []], $controller->redirectedTo);
    }

    private function makeController(): LegacyTransactionRedirectController
    {
        return new class extends LegacyTransactionRedirectController {
            public ?array $redirectedTo = null;

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }
        };
    }
}
