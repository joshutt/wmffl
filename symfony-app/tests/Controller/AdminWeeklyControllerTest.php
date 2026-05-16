<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminWeeklyController;
use App\Service\AuthenticationService;
use App\Service\MvpScoringService;
use App\Service\SeasonWeekService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class AdminWeeklyControllerTest extends TestCase
{
    // ---- GET /admin/weekly (default season/week) ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $seasonWeek, $em, $scoring);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersCorrectTemplateForCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true);

        $controller->index($auth, $seasonWeek, $em, $scoring);

        $this->assertSame('admin/weekly/index.html.twig', $controller->renderedView);
    }

    public function testIndexDefaultsToCurrentSeasonAndPreviousWeekWhenInSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true);
        $seasonWeek->method('getCurrentWeek')->willReturn(6);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $controller->index($auth, $seasonWeek, $em, $scoring);

        $this->assertSame(2025, $controller->renderedParams['season']);
        $this->assertSame(5, $controller->renderedParams['week']);
    }

    public function testIndexDefaultsToWeek1WhenCurrentWeekIsOne(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true);
        $seasonWeek->method('getCurrentWeek')->willReturn(1);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $controller->index($auth, $seasonWeek, $em, $scoring);

        $this->assertSame(1, $controller->renderedParams['week']);
    }

    public function testIndexUsesLastSeasonWeek16DuringOffseason(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true);
        $seasonWeek->method('getCurrentWeek')->willReturn(0);
        $seasonWeek->method('getPreviousWeekSeason')->willReturn(2024);
        $seasonWeek->method('getPreviousWeek')->willReturn(16);

        $controller->index($auth, $seasonWeek, $em, $scoring);

        $this->assertSame(2024, $controller->renderedParams['season']);
        $this->assertSame(16, $controller->renderedParams['week']);
    }

    // ---- GET /admin/weekly/{season}/{week} (explicit params) ----

    public function testIndexUsesExplicitSeasonAndWeekWhenProvided(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true);

        $controller->index($auth, $seasonWeek, $em, $scoring, 2023, 10);

        $this->assertSame(2023, $controller->renderedParams['season']);
        $this->assertSame(10, $controller->renderedParams['week']);
    }

    public function testIndexPassesOverallAndDefensiveListsToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true);

        $controller->index($auth, $seasonWeek, $em, $scoring, 2024, 8);

        $this->assertArrayHasKey('overall', $controller->renderedParams);
        $this->assertArrayHasKey('defense', $controller->renderedParams);
    }

    public function testIndexOverallListCappedAtTen(): void
    {
        $players = array_map(
            fn($i) => ['name' => "P$i", 'pos' => 'QB', 'abbrev' => 'XX', 'score' => (float)(100 - $i)],
            range(1, 15)
        );

        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true, scoringResult: $players);

        $controller->index($auth, $seasonWeek, $em, $scoring, 2024, 8);

        $this->assertCount(10, $controller->renderedParams['overall']);
    }

    public function testIndexDefenseListContainsOnlyDefensivePositions(): void
    {
        $players = [
            ['name' => 'QB1', 'pos' => 'QB', 'abbrev' => 'AA', 'score' => 100.0],
            ['name' => 'DB1', 'pos' => 'DB', 'abbrev' => 'BB', 'score' => 80.0],
            ['name' => 'LB1', 'pos' => 'LB', 'abbrev' => 'CC', 'score' => 70.0],
            ['name' => 'RB1', 'pos' => 'RB', 'abbrev' => 'DD', 'score' => 60.0],
            ['name' => 'DL1', 'pos' => 'DL', 'abbrev' => 'EE', 'score' => 50.0],
        ];

        [$controller, $auth, $seasonWeek, $em, $scoring] = $this->makeController(commissioner: true, scoringResult: $players);

        $controller->index($auth, $seasonWeek, $em, $scoring, 2024, 8);

        $defensePosValues = array_column($controller->renderedParams['defense'], 'pos');
        foreach ($defensePosValues as $pos) {
            $this->assertContains($pos, ['DL', 'LB', 'DB']);
        }
        $this->assertCount(3, $controller->renderedParams['defense']);
    }

    public function testIndexQueriesDatabaseForSingleWeekOnly(): void
    {
        [$controller, $auth, $seasonWeek, $em, $scoring, $conn] = $this->makeController(commissioner: true);

        $conn->expects($this->once())
            ->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('revisedactivations'),
                    $this->stringContains('a.week = :week')
                ),
                $this->equalTo(['season' => 2023, 'week' => 11])
            )
            ->willReturn([]);

        $controller->index($auth, $seasonWeek, $em, $scoring, 2023, 11);
    }

    // ---- Helpers ----

    private function makeController(bool $commissioner, array $scoringResult = []): array
    {
        $controller = new class extends AdminWeeklyController {
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

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $seasonWeek = $this->createStub(SeasonWeekService::class);

        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($conn);

        $scoring = $this->createStub(MvpScoringService::class);
        $scoring->method('rankPlayers')->willReturn($scoringResult);

        return [$controller, $auth, $seasonWeek, $em, $scoring, $conn];
    }
}
