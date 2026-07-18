<?php

namespace App\Controller;

use App\Service\SeasonRuleService;
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
        SeasonRuleService $seasonRules,
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
        $regWeeks = $seasonRules->getRegularSeasonWeeks($season);
        $teamData = $standingsRepository->getCurrentStandings($season, $week, $regWeeks);
        $gameData = $standingsRepository->getTeamGames($season, $week, $regWeeks);

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
