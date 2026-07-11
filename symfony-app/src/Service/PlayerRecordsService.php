<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Player record chases, ported from football/stats/{playerrecord,
 * lastplayer}.php: which single-game and single-season performances
 * this season crack the all-time positional top tens.
 *
 * The thresholds are the hardcoded historical top-ten lists carried
 * over verbatim from legacy (rank 1 first). They should eventually be
 * data-driven; per the phase spec they are ported as-is.
 *
 * lastplayer.php was a 2005-era snapshot of the same page (old
 * `players` table, season fixed at 2005). The legacy file had a parse
 * error (`<?php}?>`) and 500ed; this port renders what it intended.
 */
class PlayerRecordsService
{
    public const GAME_THRESHOLDS = [
        'QB' => [53, 45, 44, 44, 43, 43, 41, 41, 41, 40, 40, 40, 40],
        'RB' => [49, 49, 46, 45, 45, 45, 44, 42, 41, 41],
        'WR' => [52, 43, 41, 40, 40, 39, 39, 39, 39, 38, 38],
        'TE' => [36, 35, 32, 31, 29, 28, 26, 26, 26, 25, 25, 25, 25, 25, 25],
        'K' => [25, 23, 23, 23, 23, 22, 22, 21, 21, 21, 21, 21, 21, 21],
        'OL' => [34, 33, 33, 31, 30, 29, 29, 28, 28, 28, 28],
        'DL' => [28, 27, 26, 26, 25, 25, 25, 24, 24, 23, 23, 23, 23, 23],
        'LB' => [44, 32, 31, 31, 31, 30, 29, 29, 29, 29],
        'DB' => [43, 40, 35, 32, 30, 30, 29, 29, 29, 28, 28, 28],
    ];

    public const SEASON_THRESHOLDS = [
        'HC' => [89, 87, 80, 80, 75, 74, 74, 71, 69, 68, 68, 68],
        'QB' => [348, 345, 325, 322, 291, 289, 287, 280, 279, 271, 271],
        'RB' => [333, 302, 271, 262, 241, 231, 227, 224, 216, 215],
        'WR' => [202, 193, 192, 192, 181, 177, 175, 175, 175, 174, 174],
        'TE' => [168, 138, 133, 133, 131, 126, 122, 118, 116, 113],
        'K' => [158, 155, 151, 149, 147, 146, 138, 138, 138, 137, 137],
        'OL' => [154, 151, 151, 150, 147, 146, 142, 141, 138, 131],
        'DL' => [167, 133, 125, 117, 116, 116, 110, 109, 108, 107, 107],
        'LB' => [164, 159, 159, 153, 148, 146, 145, 143, 139, 138, 138],
        'DB' => [149, 147, 146, 137, 137, 132, 124, 124, 123, 122],
    ];

    public const LASTPLAYER_GAME_THRESHOLDS = [
        'QB' => [41, 40, 40, 39, 39, 38, 38, 36, 36, 36, 36],
        'RB' => [49, 46, 45, 44, 42, 41, 35, 35, 35, 35, 35],
        'WR' => [52, 40, 39, 39, 37, 36, 36, 36, 36, 35, 35, 35],
        'TE' => [36, 35, 32, 29, 26, 26, 25, 25, 24, 24],
        'K' => [23, 23, 23, 21, 21, 20, 20, 19, 19, 19, 19, 19],
        'OL' => [33, 31, 29, 28, 26, 25, 25, 24, 23, 23],
        'DL' => [26, 25, 23, 23, 23, 22, 21, 20, 20, 20, 20, 20, 20],
        'LB' => [44, 33, 31, 30, 29, 29, 28, 26, 24, 24, 24, 24],
        'DB' => [29, 28, 27, 27, 26, 26, 26, 26, 26, 26],
    ];

    public const LASTPLAYER_SEASON_THRESHOLDS = [
        'HC' => [51, 51, 51, 51, 49, 49, 47, 46, 46, 45],
        'QB' => [322, 289, 287, 253, 228, 216, 210, 205, 203, 196],
        'RB' => [302, 271, 262, 241, 227, 216, 209, 203, 197, 194],
        'WR' => [193, 192, 192, 181, 175, 175, 175, 174, 173, 173],
        'TE' => [133, 133, 112, 101, 96, 94, 93, 88, 84, 72],
        'K' => [158, 138, 138, 134, 132, 132, 129, 128, 120],
        'OL' => [151, 147, 146, 141, 138, 129, 124, 122, 118, 114],
        'DL' => [125, 107, 103, 99, 97, 92, 91, 90, 87, 86],
        'LB' => [164, 159, 159, 153, 148, 135, 131, 124, 124, 123],
        'DB' => [149, 146, 137, 123, 122, 114, 113, 111, 110, 109],
    ];

    public const LASTPLAYER_SEASON = 2005;

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @return array{game: array, season: array} record entries per kind
     */
    public function getRecords(int $season): array
    {
        return $this->buildRecords($season, self::GAME_THRESHOLDS, self::SEASON_THRESHOLDS, 'newplayers', 'pos');
    }

    /** The 2005 snapshot page, against the retired `players` table */
    public function getLastPlayerRecords(): array
    {
        return $this->buildRecords(
            self::LASTPLAYER_SEASON,
            self::LASTPLAYER_GAME_THRESHOLDS,
            self::LASTPLAYER_SEASON_THRESHOLDS,
            'players',
            'position'
        );
    }

    /**
     * @return array{game: array, season: array} record entries per kind
     */
    private function buildRecords(
        int $season,
        array $gameThresholds,
        array $seasonThresholds,
        string $playerTable,
        string $posColumn
    ): array {
        $game = [];
        foreach ($gameThresholds as $pos => $thresholds) {
            $rows = $this->connection->fetchAllAssociative(
                "SELECT CONCAT(p.firstname, ' ', p.lastname) as name, wm.weekname as week, ps.active as pts
                 FROM $playerTable p, playerscores ps, weekmap wm
                 WHERE p.playerid=ps.playerid
                 AND wm.season=ps.season AND wm.week=ps.week
                 AND wm.season = :season
                 AND p.$posColumn = :pos
                 AND ps.active >= :min
                 ORDER BY ps.active DESC, ps.week",
                ['season' => $season, 'pos' => $pos, 'min' => min($thresholds)]
            );
            $game = array_merge($game, $this->rankAgainstThresholds($pos, $thresholds, $rows));
        }

        $seasonRecords = [];
        foreach ($seasonThresholds as $pos => $thresholds) {
            $rows = $this->connection->fetchAllAssociative(
                "SELECT CONCAT(p.firstname, ' ', p.lastname) as name, sum(ps.active) as pts
                 FROM $playerTable p, playerscores ps
                 WHERE p.playerid=ps.playerid
                 AND ps.season = :season
                 AND p.$posColumn = :pos
                 GROUP BY p.playerid
                 HAVING `pts` >= :min
                 ORDER BY `pts` DESC",
                ['season' => $season, 'pos' => $pos, 'min' => min($thresholds)]
            );
            $seasonRecords = array_merge($seasonRecords, $this->rankAgainstThresholds($pos, $thresholds, $rows));
        }

        return ['game' => $game, 'season' => $seasonRecords];
    }

    /**
     * Walk the season's qualifying performances (ordered pts desc)
     * against a position's all-time top ten. Each performance that beats
     * or ties a slot takes a rank; later this-season performances push
     * rank numbers down (the legacy soft-adjustment), and everything
     * past rank 10 is cut. The first performance that places nowhere
     * ends the position.
     *
     * @param int[] $thresholds rank 1 first
     * @param array<int, array{name: string, pts: mixed, week?: string}> $rows
     * @return array<int, array{pos: string, name: string, week: ?string, pts: mixed, rank: int, tie: bool}>
     */
    public function rankAgainstThresholds(string $pos, array $thresholds, array $rows): array
    {
        $records = [];
        $count = 1;
        $adjustment = 0;
        $softAdjustment = 0;
        $lastChange = 999;

        foreach ($rows as $row) {
            for ($i = $count; $i <= count($thresholds); $i++) {
                if ($row['pts'] < $lastChange) {
                    $adjustment = $softAdjustment;
                    $lastChange = $row['pts'];
                }
                if ($i + $adjustment > 10) {
                    break;
                }
                if ($row['pts'] >= $thresholds[$i - 1]) {
                    $records[] = [
                        'pos' => $pos,
                        'name' => $row['name'],
                        'week' => $row['week'] ?? null,
                        'pts' => $row['pts'],
                        'rank' => $i + $adjustment,
                        'tie' => $row['pts'] == $thresholds[$i - 1],
                    ];
                    $softAdjustment++;
                    $count = $i;
                    continue 2;
                }
            }
            break;
        }

        return $records;
    }
}
