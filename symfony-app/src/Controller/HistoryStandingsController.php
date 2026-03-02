<?php

namespace App\Controller;

use App\Repository\StandingsRepository;
use App\Service\StandingsCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryStandingsController extends AbstractController
{
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
        $teams = $calculatorService->buildTeamArray(
            $standingsRepository->getCurrentStandings($season, $week),
            $standingsRepository->getTeamGames($season, $week)
        );
        $calculatorService->sortTeams($teams);

        return $this->render('history/standings.html.twig', compact('teams', 'season', 'week'));
    }
}
