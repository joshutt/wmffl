<?php

namespace App\Controller;

use App\Repository\StandingsRepository;
use App\Service\StandingsCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $teamData = $standingsRepository->getCurrentStandings($season, $week);
        $gameData = $standingsRepository->getTeamGames($season, $week);

        $teams = $calculatorService->buildTeamArray($teamData, $gameData);
        $calculatorService->sortTeams($teams);

        return $this->render('history/standings.html.twig', [
            'teams' => $teams,
            'season' => $season,
            'week' => $week,
        ]);
    }
}
