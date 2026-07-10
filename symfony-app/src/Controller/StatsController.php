<?php

namespace App\Controller;

use App\Repository\StatsRepository;
use App\Service\SeasonWeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Stats index, league leaders, player stats and the CSV feeds, ported
 * from football/stats/{index,leaders,playerstats,statcsv,playerlist}.php.
 *
 * Like legacy reportUtils.php, the table pages answer several formats:
 * html (full page), ajax (bare table for the in-page selectors), csv
 * (download) and json.
 */
class StatsController extends AbstractController
{
    public function __construct(
        private readonly StatsRepository $stats,
        private readonly SeasonWeekService $seasonWeek
    ) {
    }

    #[Route('/stats', name: 'stats_index')]
    public function index(): Response
    {
        return $this->render('stats/index.html.twig');
    }

    #[Route('/stats/leaders', name: 'stats_leaders', methods: ['GET', 'POST'])]
    public function leaders(Request $request): Response
    {
        $season = $this->requestedSeason($request);
        $titles = array_merge(['Team'], StatsRepository::POSITIONS, ['Offense', 'Defense', 'Total Pts']);
        $rows = array_map('array_values', $this->stats->getLeaders($season));

        return match ($this->requestedFormat($request)) {
            'csv' => $this->csvResponse($titles, $rows, 'leaders.csv'),
            'json' => $this->jsonRows($titles, $rows),
            'ajax' => $this->render('stats/_table.html.twig', ['titles' => $titles, 'rows' => $rows]),
            default => $this->render('stats/leaders.html.twig', [
                'titles' => $titles,
                'rows' => $rows,
                'week' => $this->stats->getMaxScoredWeek($season),
            ]),
        };
    }

    #[Route('/stats/players', name: 'stats_players', methods: ['GET', 'POST'])]
    public function players(Request $request): Response
    {
        $pos = $this->param($request, 'pos') ?: 'QB';
        if (!isset(StatsRepository::POS_MAP[$pos])) {
            $pos = 'QB';
        }
        $season = $this->requestedSeason($request);
        $format = $this->requestedFormat($request);

        $lines = $this->stats->getPlayerStats(
            $season,
            $pos,
            $this->param($request, 'sort') ?: 'ppg',
            max(1, (int) ($this->param($request, 'startWeek') ?: 1)),
            min(17, (int) ($this->param($request, 'endWeek') ?: 17)),
            // legacy statcsv's HC export had no penalty column
            hcPenalties: $format !== 'csv'
        );

        $statAliases = array_keys(StatsRepository::POS_MAP[$pos]);
        if ($pos === 'HC' && $format === 'csv') {
            array_pop($statAliases);
        }
        $titles = array_merge(
            ['Name', 'NFL Team', 'Bye', 'FF Team', 'G', 'Pts', 'PPG'],
            array_slice(StatsRepository::POS_LABELS[$pos], 0, count($statAliases))
        );

        $rows = [];
        foreach ($lines as $line) {
            $row = [$line['name'], $line['team'], $line['bye'], $line['ffteam'], $line['games'], $line['pts'], $line['ppg']];
            foreach ($statAliases as $alias) {
                $row[] = $line[$alias];
            }
            $rows[] = $row;
        }

        return match ($format) {
            'csv' => $this->csvResponse($titles, $rows, "playerstats-$pos.csv"),
            'json' => $this->jsonRows($titles, $rows),
            'ajax' => $this->render('stats/_table.html.twig', ['titles' => $titles, 'rows' => $rows]),
            default => $this->render('stats/players.html.twig', [
                'titles' => $titles,
                'rows' => $rows,
                'pos' => $pos,
                'positions' => StatsRepository::POSITIONS,
            ]),
        };
    }

    /** Plain-text weekly score feed (playerlist.php) */
    #[Route('/stats/playerlist', name: 'stats_playerlist')]
    public function playerlist(): Response
    {
        $lines = ["Last Name,First Name,Pos,NFL,Week,Pts"];
        foreach ($this->stats->getActivePlayerScores($this->seasonWeek->getCurrentSeason()) as $row) {
            $lines[] = "{$row['lastname']},{$row['firstname']},{$row['pos']},{$row['team']},{$row['week']},{$row['pts']}";
        }

        return new Response(implode("\n", $lines) . "\n", headers: ['Content-Type' => 'text/plain']);
    }

    // ---- shared helpers ----

    /** Query param with POST fallback, matching legacy $_REQUEST reads */
    private function param(Request $request, string $key): ?string
    {
        $value = $request->query->get($key, $request->request->get($key));

        return $value === null ? null : (string) $value;
    }

    /** During the offseason the pages default to the completed season */
    private function requestedSeason(Request $request): int
    {
        if ($season = (int) $this->param($request, 'season')) {
            return $season;
        }

        $season = $this->seasonWeek->getCurrentSeason();

        return $this->seasonWeek->getCurrentWeek() < 1 ? $season - 1 : $season;
    }

    private function requestedFormat(Request $request): string
    {
        $format = strtolower((string) $this->param($request, 'format'));

        return in_array($format, ['csv', 'json', 'ajax'], true) ? $format : 'html';
    }

    private function csvResponse(array $titles, array $rows, string $filename): Response
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $titles, escape: '\\');
        foreach ($rows as $row) {
            fputcsv($handle, $row, escape: '\\');
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, headers: [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    private function jsonRows(array $titles, array $rows): JsonResponse
    {
        return new JsonResponse(array_map(
            fn (array $row) => array_combine($titles, array_slice(array_values($row), 0, count($titles))),
            $rows
        ));
    }
}
