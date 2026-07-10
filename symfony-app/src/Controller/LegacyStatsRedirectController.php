<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/stats/ URLs to the Symfony
 * routes, with query-param carry-through for the history-page deep
 * links (/stats/leaders?season=...). GET+POST is accepted where the
 * legacy in-page JS posted format switches.
 */
class LegacyStatsRedirectController extends AbstractController
{
    #[Route('/stats/index.php', name: 'legacy_stats_index_php')]
    public function index(): Response
    {
        return $this->redirectToRoute('stats_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/stats/leaders.php', name: 'legacy_stats_leaders_php', methods: ['GET', 'POST'])]
    public function leaders(Request $request): Response
    {
        return $this->redirectToRoute(
            'stats_leaders',
            $this->carry($request, ['season', 'format']),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route('/stats/playerstats', name: 'legacy_stats_playerstats', methods: ['GET', 'POST'])]
    #[Route('/stats/playerstats.php', name: 'legacy_stats_playerstats_php', methods: ['GET', 'POST'])]
    public function playerstats(Request $request): Response
    {
        return $this->redirectToRoute(
            'stats_players',
            $this->carry($request, ['pos', 'sort', 'season', 'format']),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    /** statcsv shared the playerstats query; its replacement is the csv format */
    #[Route('/stats/statcsv', name: 'legacy_stats_statcsv', methods: ['GET', 'POST'])]
    #[Route('/stats/statcsv.php', name: 'legacy_stats_statcsv_php', methods: ['GET', 'POST'])]
    public function statcsv(Request $request): Response
    {
        return $this->redirectToRoute(
            'stats_players',
            $this->carry($request, ['pos', 'sort', 'season', 'startWeek', 'endWeek']) + ['format' => 'csv'],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route('/stats/playerlist.php', name: 'legacy_stats_playerlist_php')]
    public function playerlist(): Response
    {
        return $this->redirectToRoute('stats_playerlist', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/stats/weekbyweek.php', name: 'legacy_stats_weekbyweek_php', methods: ['GET', 'POST'])]
    public function weekbyweek(Request $request): Response
    {
        return $this->redirectToRoute(
            'stats_weekbyweek',
            $this->carry($request, ['team', 'pos', 'season', 'format']),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

    #[Route('/stats/powerrate', name: 'legacy_stats_powerrate')]
    #[Route('/stats/powerrate.php', name: 'legacy_stats_powerrate_php')]
    #[Route('/stats/powerlist', name: 'legacy_stats_powerlist')]
    #[Route('/stats/powerlist.php', name: 'legacy_stats_powerlist_php')]
    public function power(): Response
    {
        return $this->redirectToRoute('stats_power', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/stats/luck.php', name: 'legacy_stats_luck_php')]
    public function luck(): Response
    {
        return $this->redirectToRoute('stats_luck', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/stats/injuryReport', name: 'legacy_stats_injuryreport')]
    #[Route('/stats/injuryReport.php', name: 'legacy_stats_injuryreport_php')]
    public function injuryReport(): Response
    {
        return $this->redirectToRoute('stats_injuries', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/stats/playerrecord', name: 'legacy_stats_playerrecord')]
    #[Route('/stats/playerrecord.php', name: 'legacy_stats_playerrecord_php')]
    public function playerrecord(): Response
    {
        return $this->redirectToRoute('stats_records', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/stats/lastplayer.php', name: 'legacy_stats_lastplayer_php')]
    public function lastplayer(): Response
    {
        return $this->redirectToRoute('stats_lastplayer', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Deleted, not migrated: info.php (a phpinfo() dump — security
     * liability), the 2009-hardcoded standings.php + its weekstandings
     * include, and the unlinked teamcompare.php. They land on the stats
     * index rather than executing anything.
     */
    #[Route('/stats/info.php', name: 'legacy_stats_info_php')]
    #[Route('/stats/standings.php', name: 'legacy_stats_standings_php')]
    #[Route('/stats/weekstandings.php', name: 'legacy_stats_weekstandings_php')]
    #[Route('/stats/teamcompare.php', name: 'legacy_stats_teamcompare_php')]
    public function deadPages(): Response
    {
        return $this->redirectToRoute('stats_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /** @param string[] $keys */
    private function carry(Request $request, array $keys): array
    {
        $params = [];
        foreach ($keys as $key) {
            $value = $request->query->get($key, $request->request->get($key));
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
