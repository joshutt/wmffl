<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminTooManyPlayersController;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminTooManyPlayersControllerTest extends TestCase
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

        $this->assertSame('admin/toomanplayers/index.html.twig', $controller->renderedView);
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

    public function testIndexPassesRowsToTemplate(): void
    {
        $rows = [
            ['weekname' => 'Week 1', 'name' => 'Team A', 'playerCount' => 27, 'week' => 1],
            ['weekname' => 'Week 2', 'name' => 'Team B', 'playerCount' => 28, 'week' => 2],
        ];

        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, rows: $rows);

        $controller->index($auth, $seasonWeek, $em, 2025);

        $this->assertSame($rows, $controller->renderedParams['rows']);
    }

    public function testIndexQueriesWithCorrectSeasonAndLimit(): void
    {
        [$controller, $auth, $seasonWeek, $em, $conn] = $this->makeController(commissioner: true);

        $conn->expects($this->once())
            ->method('fetchAllAssociative')
            ->with(
                $this->stringContains('roster'),
                $this->equalTo(['season' => 2024, 'limit' => 26])
            )
            ->willReturn([]);

        $controller->index($auth, $seasonWeek, $em, 2024);
    }

    // ---- Helpers ----

    private Connection $conn;

    private function makeController(bool $commissioner, array $rows = []): array
    {
        $controller = new class extends AdminTooManyPlayersController {
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
        $this->conn->method('fetchAllAssociative')->willReturn($rows);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->conn);

        return [$controller, $auth, $seasonWeek, $em, $this->conn];
    }
}
