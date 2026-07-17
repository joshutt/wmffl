<?php

namespace App\Tests\Controller;

use App\Controller\HomeController;
use App\Repository\ArticleRepository;
use App\Repository\QuickLinkRepository;
use App\Repository\ScoresRepository;
use App\Repository\StandingsRepository;
use App\Service\SeasonWeekService;
use App\Service\StandingsCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class HomeControllerTest extends TestCase
{
    public function testIndexRendersHomepageTemplate(): void
    {
        [$controller, $deps] = $this->makeController(week: 5);

        $controller->index(...$deps);

        $this->assertSame('home/index.html.twig', $controller->renderedView);
        foreach (['articles', 'scores', 'teams', 'posts', 'season', 'quicklinks'] as $key) {
            $this->assertArrayHasKey($key, $controller->renderedParams);
        }
    }

    public function testIndexUsesCurrentSeasonDuringSeason(): void
    {
        [$controller, $deps] = $this->makeController(week: 5);
        [, , $scores, $standings] = $deps;
        $scores->expects($this->once())->method('getLatestWeekScores')->with(2025);
        $standings->expects($this->once())->method('getCurrentStandings')->with(2025, 5);

        $controller->index(...$deps);

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testIndexFallsBackToPreviousSeasonInOffSeason(): void
    {
        [$controller, $deps] = $this->makeController(week: 0);
        [, , $scores, $standings] = $deps;
        $scores->expects($this->once())->method('getLatestWeekScores')->with(2024);
        $standings->expects($this->once())->method('getCurrentStandings')->with(2024, 16);

        $controller->index(...$deps);

        // quicklinks still use the actual current season
        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testIndexShowsFourArticles(): void
    {
        [$controller, $deps] = $this->makeController(week: 5);
        [, $articles] = $deps;
        $articles->expects($this->once())->method('findActivePage')->with(4)->willReturn([]);

        $controller->index(...$deps);
    }

    // ---- Helpers ----

    private function makeController(int $week): array
    {
        $controller = new class extends HomeController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }
        };

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);
        $seasonWeek->method('getCurrentWeek')->willReturn($week);

        $articles = $this->createMock(ArticleRepository::class);
        $articles->method('findActivePage')->willReturn([]);

        $scores = $this->createMock(ScoresRepository::class);
        $scores->method('getLatestWeekScores')->willReturn(null);

        $standings = $this->createMock(StandingsRepository::class);
        $standings->method('getCurrentStandings')->willReturn([]);
        $standings->method('getTeamGames')->willReturn([]);

        $calculator = $this->createStub(StandingsCalculatorService::class);
        $calculator->method('buildTeamArray')->willReturn([]);

        $quickLinks = $this->createMock(QuickLinkRepository::class);
        $quickLinks->method('findVisible')->willReturn([]);

        $query = $this->createMock(Query::class);
        $query->method('setMaxResults')->willReturnSelf();
        $query->method('getResult')->willReturn([]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('createQuery')->willReturn($query);

        return [$controller, [$seasonWeek, $articles, $scores, $standings, $calculator, $quickLinks, $em]];
    }
}
