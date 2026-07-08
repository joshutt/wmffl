<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/teams/ URLs to the Symfony
 * team routes. `viewteam` is resolved the way teamheader.php did —
 * team id, abbreviation, or lowercased/space-stripped name — and a
 * missing or unresolvable value lands on the teams index rather than
 * legacy's silent default of team 2.
 */
class LegacyTeamRedirectController extends AbstractController
{
    public function __construct(
        private readonly TeamRepository $teams
    ) {
    }

    #[Route('/teams/teamroster', name: 'legacy_team_roster')]
    #[Route('/teams/teamroster.php', name: 'legacy_team_roster_php')]
    public function roster(Request $request): Response
    {
        return $this->redirectToTeamRoute($request, 'team_roster');
    }

    /** The .php aliases cover archival pages and old bookmarks */
    #[Route('/teams/teamschedule', name: 'legacy_team_schedule')]
    #[Route('/teams/teamschedule.php', name: 'legacy_team_schedule_php')]
    public function schedule(Request $request): Response
    {
        $params = [];
        if ($vsTeam = $request->query->get('vsTeam')) {
            $params['vs'] = (int) $vsTeam;
        }

        return $this->redirectToTeamRoute($request, 'team_schedule', $params);
    }

    #[Route('/teams/teamhistory', name: 'legacy_team_history')]
    #[Route('/teams/teamhistory.php', name: 'legacy_team_history_php')]
    public function history(Request $request): Response
    {
        return $this->redirectToTeamRoute($request, 'team_history');
    }

    #[Route('/teams/squirrels.php', name: 'legacy_team_squirrels_php')]
    public function squirrels(): Response
    {
        return $this->redirectToRoute('team_squirrels', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /** The legacy compare form POSTed to itself, so accept both methods */
    #[Route('/teams/compareteams', name: 'legacy_team_compare', methods: ['GET', 'POST'])]
    #[Route('/teams/compareteams.php', name: 'legacy_team_compare_php', methods: ['GET', 'POST'])]
    public function compare(Request $request): Response
    {
        $params = [];
        foreach (['teamone', 'teamtwo'] as $key) {
            if ($value = $request->query->get($key, $request->request->get($key))) {
                $params[$key] = (int) $value;
            }
        }

        return $this->redirectToRoute('team_compare', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    private function redirectToTeamRoute(Request $request, string $route, array $params = []): Response
    {
        $viewteam = (string) $request->query->get('viewteam', '');
        $teamId = $viewteam === '' ? null : $this->teams->resolveTeamId($viewteam);

        if ($teamId === null) {
            return $this->redirectToRoute('team_index', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->redirectToRoute($route, ['id' => $teamId] + $params, Response::HTTP_MOVED_PERMANENTLY);
    }
}
