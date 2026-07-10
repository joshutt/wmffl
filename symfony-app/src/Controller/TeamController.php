<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use App\Service\SeasonWeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeamController extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teams,
        private readonly SeasonWeekService $seasonWeek
    ) {
    }

    #[Route('/teams', name: 'team_index')]
    public function index(): Response
    {
        $byDivision = [];
        foreach ($this->teams->getTeamsByDivision($this->seasonWeek->getCurrentSeason()) as $team) {
            $byDivision[$team['division']][] = $team;
        }
        ksort($byDivision);

        return $this->render('team/index.html.twig', [
            'divisions' => $byDivision,
        ]);
    }

    #[Route('/teams/squirrels', name: 'team_squirrels')]
    public function squirrels(): Response
    {
        return $this->render('team/squirrels.html.twig');
    }

    #[Route('/teams/compare', name: 'team_compare')]
    public function compare(Request $request): Response
    {
        $teamOne = $request->query->getInt('teamone');
        $teamTwo = $request->query->getInt('teamtwo');

        $rosters = null;
        if ($teamOne && $teamTwo) {
            $rosters = [];
            foreach ($this->teams->getRostersForComparison($teamOne, $teamTwo) as $player) {
                $rosters[$player['teamname']][] = $player;
            }
        }

        return $this->render('team/compare.html.twig', [
            'teams' => $this->teams->getActiveTeams(),
            'teamOne' => $teamOne,
            'teamTwo' => $teamTwo,
            'rosters' => $rosters,
        ]);
    }

    #[Route('/team/{id}', name: 'team_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        return $this->redirectToRoute('team_roster', ['id' => $id], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/team/{id}/roster', name: 'team_roster', requirements: ['id' => '\d+'])]
    public function roster(int $id): Response
    {
        $currentSeason = $this->seasonWeek->getCurrentSeason();

        // Season-boundary flip (legacy roster.php:69-74): through week 1 the
        // points column still shows last season and the cost column this
        // season's protection prices; afterwards both roll forward one.
        $atBoundary = $this->seasonWeek->getCurrentWeek() <= 1;

        return $this->render('team/roster.html.twig', $this->headerParams($id, 'Roster') + [
            'roster' => $this->teams->getCurrentRoster($id),
            'summary' => $this->teams->getTransactionSummary($id, $currentSeason),
            'costSeason' => $atBoundary ? $currentSeason : $currentSeason + 1,
            'ptsSeason' => $atBoundary ? $currentSeason - 1 : $currentSeason,
        ]);
    }

    #[Route('/team/{id}/schedule/{season?}', name: 'team_schedule', requirements: ['id' => '\d+', 'season' => '\d+'])]
    public function schedule(int $id, ?int $season, Request $request): Response
    {
        $params = $this->headerParams($id, 'Schedule');

        if ($vs = $request->query->getInt('vs')) {
            return $this->headToHead($id, $vs, $params);
        }

        // The season dropdown submits ?season= as its no-JS fallback; the
        // canonical URL puts the season in the path.
        $season ??= $request->query->getInt('season') ?: null;

        // Completed weeks are those before $checkWeek; past seasons show
        // everything, and week 0 rolls the default season back one
        // (legacy indschedule.php:11-20).
        $checkWeek = 17;
        if ($season === null) {
            $season = $this->seasonWeek->getCurrentSeason();
            $checkWeek = $this->seasonWeek->getCurrentWeek();
            if ($checkWeek === 0) {
                $season--;
                $checkWeek = 17;
            }
        }

        return $this->render('team/schedule.html.twig', $params + [
            'season' => $season,
            'checkWeek' => $checkWeek,
            'games' => $this->teams->getSeasonSchedule($id, $season),
            'seasons' => $this->teams->getSeasonsPlayed($id),
            'opponents' => $this->teams->getOpponentList(),
        ]);
    }

    private function headToHead(int $id, int $vs, array $params): Response
    {
        $opponents = $this->teams->getOpponentList();
        $opponentName = null;
        foreach ($opponents as $opponent) {
            if ((int) $opponent['teamid'] === $vs) {
                $opponentName = $opponent['name'];
            }
        }
        if ($opponentName === null) {
            throw $this->createNotFoundException("No opponent with id $vs");
        }

        // In-progress-week hiding (legacy h2h.php:3-14, 98): during week 0
        // everything is complete; otherwise current-season games from the
        // current week on show no result.
        $currentSeason = $this->seasonWeek->getCurrentSeason();
        $checkWeek = $this->seasonWeek->getCurrentWeek() ?: 17;

        return $this->render('team/h2h.html.twig', $params + [
            'opponentName' => $opponentName,
            'opponents' => $opponents,
            'vs' => $vs,
            'games' => $this->teams->getHeadToHead($id, $vs),
            'record' => $this->teams->getHeadToHeadRecord($id, $vs),
            'currentSeason' => $currentSeason,
            'checkWeek' => $checkWeek,
        ]);
    }

    #[Route('/team/{id}/history', name: 'team_history', requirements: ['id' => '\d+'])]
    public function history(int $id): Response
    {
        $seasonRecords = $this->teams->getRegularSeasonRecords(
            $id,
            $this->seasonWeek->getCurrentWeek(),
            $this->seasonWeek->getCurrentSeason()
        );

        return $this->render('team/history.html.twig', $this->headerParams($id, 'History') + [
            'records' => array_merge(
                [TeamRepository::totalRecord($seasonRecords)],
                $this->teams->getPlayoffRecord($id),
                $seasonRecords
            ),
            'playoffResults' => $this->teams->getPlayoffResults($id),
            'titles' => $this->teams->getTitles($id),
            'pastOwners' => $this->teams->getPastOwners($id),
            'pastNames' => $this->teams->getPastNames($id),
        ]);
    }

    /** Shared header/linkbar params for the roster/schedule/history pages; 404s unknown teams */
    private function headerParams(int $id, string $page): array
    {
        $header = $this->teams->getTeamHeader($id);
        if (!$header) {
            throw $this->createNotFoundException("No team with id $id");
        }

        return [
            'header' => $header,
            'championships' => $this->teams->getChampionshipSeasons($id),
            'page' => $page,
        ];
    }
}
