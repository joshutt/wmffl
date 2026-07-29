<?php

namespace App\Tests\Controller;

use App\Controller\LegacyRulesRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LegacyRulesRedirectControllerTest extends TestCase
{
    private LegacyRulesRedirectController $controller;

    protected function setUp(): void
    {
        $this->controller = new class extends LegacyRulesRedirectController {
            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $qs = $parameters ? '?' . http_build_query($parameters) : '';
                return new RedirectResponse("/$route$qs", $status);
            }
        };
    }

    public function testProposalsYearRedirectsWithSeason(): void
    {
        $response = $this->controller->proposalsYear('2026');
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('/proposals_list?season=2026', $response->getTargetUrl());
    }

    public function testProposals2005RedirectsWithSeason(): void
    {
        $response = $this->controller->proposalsYear('2005');
        $this->assertSame('/proposals_list?season=2005', $response->getTargetUrl());
    }

    public function testOddProposalsFilenameStillExtractsYear(): void
    {
        $this->assertSame('/proposals_list?season=2011', $this->controller->proposalsYear('2011a')->getTargetUrl());
        $this->assertSame('/proposals_list?season=2002', $this->controller->proposalsYear('20026detail')->getTargetUrl());
    }

    public function testBallotRedirects(): void
    {
        $response = $this->controller->ballot();
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('/ballot', $response->getTargetUrl());
    }

    public function testProposeRedirectsToSubmit(): void
    {
        $response = $this->controller->propose();
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('/proposals_submit_form', $response->getTargetUrl());
    }
}
