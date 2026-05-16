<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminDraftDatesController;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminDraftDatesControllerTest extends TestCase
{
    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersCorrectTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $seasonWeek, $em);

        $this->assertSame('admin/draftdates/index.html.twig', $controller->renderedView);
    }

    public function testIndexDefaultsToCurrentSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $controller->index($auth, $seasonWeek, $em);

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testIndexUsesExplicitSeasonWhenProvided(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $seasonWeek, $em, 2023);

        $this->assertSame(2023, $controller->renderedParams['season']);
    }

    public function testIndexAggregatesYesNoCountsPerDate(): void
    {
        $rows = [
            ['name' => 'Team A', 'date' => '2025-07-12', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-12', 'attend' => 'N'],
            ['name' => 'Team A', 'date' => '2025-07-19', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-19', 'attend' => 'Y'],
        ];

        [$controller, $auth, $seasonWeek, $em] = $this->makeController(
            commissioner: true,
            dateRows: $rows
        );

        $controller->index($auth, $seasonWeek, $em, 2025);

        $dates = $controller->renderedParams['dates'];
        $this->assertSame(1, $dates['2025-07-12']['yes']);
        $this->assertSame(1, $dates['2025-07-12']['no']);
        $this->assertSame(2, $dates['2025-07-19']['yes']);
        $this->assertSame(0, $dates['2025-07-19']['no']);
    }

    public function testIndexComputesMaxYes(): void
    {
        $rows = [
            ['name' => 'Team A', 'date' => '2025-07-12', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-12', 'attend' => 'N'],
            ['name' => 'Team A', 'date' => '2025-07-19', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-19', 'attend' => 'Y'],
        ];

        [$controller, $auth, $seasonWeek, $em] = $this->makeController(
            commissioner: true,
            dateRows: $rows
        );

        $controller->index($auth, $seasonWeek, $em, 2025);

        $this->assertSame(2, $controller->renderedParams['maxYes']);
    }

    public function testIndexPassesNoVoteTeamsToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(
            commissioner: true,
            noVoteTeams: ['Team X', 'Team Y']
        );

        $controller->index($auth, $seasonWeek, $em, 2025);

        $this->assertSame(['Team X', 'Team Y'], $controller->renderedParams['noVote']);
    }

    public function testIndexQueriesWithCorrectSeasonCutoff(): void
    {
        [$controller, $auth, $seasonWeek, $em, $conn] = $this->makeController(commissioner: true);

        $conn->expects($this->once())
            ->method('fetchAllAssociative')
            ->with(
                $this->stringContains('draftdate'),
                $this->equalTo(['cutoff' => '2024-01-01'])
            )
            ->willReturn([]);

        $controller->index($auth, $seasonWeek, $em, 2024);
    }

    public function testIndexQueriesNoVoteWithCorrectSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em, $conn] = $this->makeController(commissioner: true);

        $conn->expects($this->once())
            ->method('fetchFirstColumn')
            ->with(
                $this->stringContains('draftvote'),
                $this->equalTo(['season' => 2024])
            )
            ->willReturn([]);

        $controller->index($auth, $seasonWeek, $em, 2024);
    }

    // ---- Helpers ----

    private Connection $conn;

    private function makeController(
        bool $commissioner,
        array $dateRows = [],
        array $noVoteTeams = []
    ): array {
        $controller = new class extends AdminDraftDatesController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }
        };

        $auth = $this->createMock(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $seasonWeek = $this->createMock(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $this->conn = $this->createMock(Connection::class);
        $this->conn->method('fetchAllAssociative')->willReturn($dateRows);
        $this->conn->method('fetchFirstColumn')->willReturn($noVoteTeams);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->conn);

        return [$controller, $auth, $seasonWeek, $em, $this->conn];
    }
}
