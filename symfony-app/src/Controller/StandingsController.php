<?php

namespace App\Controller;

use App\Service\SeasonWeekService;
use App\Repository\StandingsRepository;
use App\Service\StandingsCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StandingsController extends AbstractController
{
    #[Route('/standings', name: 'standings')]
    public function index(
        SeasonWeekService $seasonWeekService,
        StandingsRepository $standingsRepository,
        StandingsCalculatorService $calculatorService
    ): Response {
        $season = $seasonWeekService->getCurrentSeason();
        $week = $seasonWeekService->getCurrentWeek();

        // Handle off-season (week 0) - show previous season's final standings
        // Matches logic from football/standings.php lines 11-14
        if ($week == 0) {
            $week = 16;
            $season = $season - 1;
        }

        // Fetch data via repository
        $teamData = $standingsRepository->getCurrentStandings($season, $week);
        $gameData = $standingsRepository->getTeamGames($season, $week);

        // Build and sort WMFFL\Team objects via service
        $teamArray = $calculatorService->buildTeamArray($teamData, $gameData);
        $calculatorService->sortTeams($teamArray);

        return $this->render('standings/index.html.twig', [
            'teams' => $teamArray,
            'season' => $season,
            'week' => $week,
        ]);
    }
}
