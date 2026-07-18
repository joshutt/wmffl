<?php

namespace App\Controller;

use App\Repository\StandingsRepository;
use App\Service\SeasonRuleService;
use App\Service\SeasonWeekService;
use App\Service\StandingsCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryStandingsController extends AbstractController
{
    public function __construct(
        private readonly SeasonRuleService $seasonRules
    ) {
    }

    #[Route('/history/standings', name: 'history_standings_current')]
    public function current(
        SeasonWeekService $seasonWeekService,
        StandingsCalculatorService $calculatorService,
        StandingsRepository $standingsRepository,
    ): Response {
        $season = $seasonWeekService->getCurrentSeason();
        $week = $seasonWeekService->getCurrentWeek();

        if ($week == 0) {
            $week = 16;
            $season = $season - 1;
        }

        return $this->renderStandings($calculatorService, $standingsRepository, $season, $week);
    }

    #[Route('/history/standings/{season}/{week}', name: 'history_standings')]
    public function index(
        StandingsCalculatorService $calculatorService,
        StandingsRepository $standingsRepository,
        int $season,
        int $week = 16
    ): Response {
        return $this->renderStandings($calculatorService, $standingsRepository, $season, $week);
    }

    #[Route('/history/{season}Season/standings', name: 'history_season_standings')]
    public function seasonStandings(
        StandingsCalculatorService $calculatorService,
        StandingsRepository $standingsRepository,
        Request $request,
        int $season
    ): Response {
        return $this->renderStandings(
            $calculatorService,
            $standingsRepository,
            $season,
            $request->query->getInt('week', 16)
        );
    }

    private function renderStandings(
        StandingsCalculatorService $calculatorService,
        StandingsRepository $standingsRepository,
        int $season,
        int $week
    ): Response {
        $regWeeks = $this->seasonRules->getRegularSeasonWeeks($season);
        $teams = $calculatorService->buildTeamArray(
            $standingsRepository->getCurrentStandings($season, $week, $regWeeks),
            $standingsRepository->getTeamGames($season, $week, $regWeeks)
        );
        $calculatorService->sortTeams($teams);

        return $this->render('history/standings.html.twig', compact('teams', 'season', 'week'));
    }
}
