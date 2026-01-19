<?php

namespace App\Controller;

use App\Service\SeasonWeekService;
use App\Repository\StandingsRepository;
use WMFFL\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StandingsController extends AbstractController
{
    #[Route('/standings', name: 'standings')]
    public function index(
        SeasonWeekService $seasonWeekService,
        StandingsRepository $standingsRepository
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

        // Build WMFFL\Team objects (reuse existing class)
        $teamArray = $this->buildTeamArray($teamData, $gameData);

        // Sort using existing comparator from WMFFL\Team class
        usort($teamArray, 'WMFFL\Team::orderteam');

        return $this->render('standings/index.html.twig', [
            'teams' => $teamArray,
            'season' => $season,
            'week' => $week,
        ]);
    }

    /**
     * Build Team objects from raw data
     * Ports logic from weekstandings.php lines 60-92
     *
     * @param array $teamData Aggregated standings data
     * @param array $gameData Individual game results
     * @return Team[] Array of Team objects
     */
    private function buildTeamArray(array $teamData, array $gameData): array
    {
        $teamArray = [];

        // First pass: Create Team objects with aggregated data
        // Matches weekstandings.php lines 60-76
        foreach ($teamData as $row) {
            $t = new Team($row['team'], $row['division'], $row['teamid']);

            // Set division record
            $div = [$row['divwin'], $row['divlose'], $row['divtie']];
            $t->divRecord = $div;
            $t->divPtsFor = $row['divpf'] ?? 0;
            $t->divPtsAgt = $row['divpa'] ?? 0;

            // Set reference to all teams (needed for SOV calculation)
            $t->allRef = &$teamArray;

            $teamArray[$row['teamid']] = $t;
        }

        // Second pass: Add individual games to each team
        // Matches weekstandings.php lines 84-92
        foreach ($gameData as $row) {
            $teamid = $row['teamid'];
            $opp = $row['oppid'];
            $pts = $row['ptsfor'];
            $agst = $row['ptsagt'];
            $oppDiv = $row['oppdiv'];

            // addGame will update record, ptsFor, ptsAgt automatically
            $teamArray[$teamid]->addGame($opp, $pts, $agst, $oppDiv);
        }

        // Convert associative array to indexed array for usort
        return array_values($teamArray);
    }
}
