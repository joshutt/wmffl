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

    /**
     * Pre-2003 single-season records that predate the playerscores data,
     * carried verbatim from legacy recordseason.php ($qbList etc.).
     * These are the same historical dataset the SEASON_THRESHOLDS above
     * reduce to bare numbers — kept side by side so the two copies
     * can't silently drift. Rank 1 first within each position.
     */
    public const SUPPLEMENTAL_SEASON_RECORDS = [
        'HC' => [],
        'QB' => [['name' => 'Steve Young', 'season' => 1994, 'pts' => 287]],
        'RB' => [
            ['name' => 'Emmitt Smith', 'season' => 1995, 'pts' => 262],
            ['name' => 'Terrell Davis', 'season' => 1998, 'pts' => 241],
            ['name' => 'Edgerrin James', 'season' => 2000, 'pts' => 216],
            ['name' => 'Marshall Faulk', 'season' => 2000, 'pts' => 209],
            ['name' => 'Barry Sanders', 'season' => 1997, 'pts' => 203],
        ],
        'WR' => [
            ['name' => 'Cris Carter', 'season' => 1995, 'pts' => 192],
            ['name' => 'Marvin Harrison', 'season' => 2001, 'pts' => 192],
            ['name' => 'Herman Moore', 'season' => 1995, 'pts' => 181],
            ['name' => 'Jerry Rice', 'season' => 1995, 'pts' => 175],
            ['name' => 'Terrell Owens', 'season' => 2001, 'pts' => 175],
            ['name' => 'Jerry Rice', 'season' => 1993, 'pts' => 173],
            ['name' => 'Marvin Harrison', 'season' => 1999, 'pts' => 173],
        ],
        'TE' => [
            ['name' => 'Tony Gonzalez', 'season' => 2000, 'pts' => 133],
            ['name' => 'Ben Coates', 'season' => 1994, 'pts' => 112],
        ],
        'K' => [['name' => 'Sebastian Janikowski', 'season' => 2002, 'pts' => 138]],
        'OL' => [
            ['name' => 'Pittsburgh Steelers', 'season' => 2001, 'pts' => 151],
            ['name' => 'Pittsburgh Steelers', 'season' => 1997, 'pts' => 147],
            ['name' => 'Denver Broncos', 'season' => 1998, 'pts' => 146],
        ],
        'DL' => [
            ['name' => 'Michael Strahan', 'season' => 2001, 'pts' => 125],
            ['name' => 'Jason Taylor', 'season' => 2002, 'pts' => 103],
            ['name' => 'John Abraham', 'season' => 2001, 'pts' => 99],
        ],
        'LB' => [
            ['name' => 'Ray Lewis', 'season' => 1999, 'pts' => 159],
            ['name' => 'Derrick Brooks', 'season' => 2002, 'pts' => 159],
            ['name' => 'Brian Urlacher', 'season' => 2001, 'pts' => 148],
            ['name' => 'Jeremiah Trotter', 'season' => 2001, 'pts' => 135],
            ['name' => 'Ray Lewis', 'season' => 1997, 'pts' => 131],
            ['name' => 'Junior Seau', 'season' => 1994, 'pts' => 124],
            ['name' => 'Brian Urlacher', 'season' => 2002, 'pts' => 124],
            ['name' => 'London Fletcher', 'season' => 2001, 'pts' => 123],
        ],
        'DB' => [
            ['name' => 'Rodney Harrison', 'season' => 1997, 'pts' => 146],
            ['name' => 'Rodney Harrison', 'season' => 2000, 'pts' => 123],
            ['name' => 'Sammy Knight', 'season' => 2002, 'pts' => 122],
        ],
    ];

    /**
     * Pre-2003 single-week records, carried verbatim from legacy
     * recordsweek.php. Same relationship to GAME_THRESHOLDS as above.
     */
    public const SUPPLEMENTAL_GAME_RECORDS = [
        'QB' => [],
        'RB' => [['name' => 'Mike Anderson', 'season' => 2000, 'week' => 14, 'pts' => 41, 'nfl' => 'DEN', 'team' => 'MM']],
        'WR' => [
            ['name' => 'Jimmy Smith', 'season' => 2000, 'week' => 2, 'pts' => 52, 'nfl' => 'JAC', 'team' => 'ZEN'],
            ['name' => 'Jerry Rice', 'season' => 1994, 'week' => 12, 'pts' => 40, 'nfl' => 'SF', 'team' => 'NOR'],
            ['name' => 'Sterling Sharpe', 'season' => 1993, 'week' => 8, 'pts' => 39, 'nfl' => 'GB', 'team' => 'SLA'],
            ['name' => 'Jerry Rice', 'season' => 1993, 'week' => 11, 'pts' => 39, 'nfl' => 'SF', 'team' => 'BAR'],
        ],
        'TE' => [
            ['name' => 'Shannon Sharpe', 'season' => 1996, 'week' => 6, 'pts' => 36, 'nfl' => 'DEN', 'team' => 'BAR'],
            ['name' => 'Ben Coates', 'season' => 1994, 'week' => 1, 'pts' => 26, 'nfl' => 'NE', 'team' => 'WAR'],
        ],
        'K' => [
            ['name' => 'Gary Anderson', 'season' => 1998, 'week' => 15, 'pts' => 23, 'nfl' => 'MIN', 'team' => 'WER'],
            ['name' => 'Jeff Wilkins', 'season' => 2000, 'week' => 5, 'pts' => 23, 'nfl' => 'STL', 'team' => 'NOR'],
        ],
        'OL' => [
            ['name' => 'St. Louis Rams', 'season' => 2001, 'week' => 9, 'pts' => 33, 'nfl' => 'STL', 'team' => 'HEM'],
            ['name' => 'San Francisco 49ers', 'season' => 1998, 'week' => 15, 'pts' => 29, 'nfl' => 'SF', 'team' => 'WER'],
        ],
        'DL' => [
            ['name' => 'Tony Brackens', 'season' => 1999, 'week' => 12, 'pts' => 26, 'nfl' => 'JAC', 'team' => 'CRU'],
            ['name' => 'Michael Strahan', 'season' => 1998, 'week' => 1, 'pts' => 23, 'nfl' => 'NYG', 'team' => 'WER'],
            ['name' => 'Chris Doleman', 'season' => 1996, 'week' => 12, 'pts' => 22, 'nfl' => 'SF', 'team' => 'FS'],
        ],
        'LB' => [
            ['name' => 'Ken Norton', 'season' => 1995, 'week' => 8, 'pts' => 44, 'nfl' => 'SF', 'team' => 'IRA'],
            ['name' => 'Brian Urlacher', 'season' => 2001, 'week' => 4, 'pts' => 30, 'nfl' => 'CHI', 'team' => 'HEM'],
            ['name' => 'Donnie Edwards', 'season' => 1999, 'week' => 15, 'pts' => 29, 'nfl' => 'KC', 'team' => 'BAR'],
        ],
        'DB' => [
            ['name' => 'Darren Woodson', 'season' => 1995, 'week' => 5, 'pts' => 29, 'nfl' => 'DAL', 'team' => 'IRA'],
            ['name' => 'Ronde Barber', 'season' => 2001, 'week' => 15, 'pts' => 28, 'nfl' => 'TB', 'team' => 'ZEN'],
        ],
    ];

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Interleave a position's supplemental pre-2003 records into its
     * DB-derived top-30 and apply the legacy top-10-plus-ties cutoff —
     * a faithful port of printRankList() from recordseason.php /
     * recordsweek.php, including its quirks: the displayed rank keeps
     * counting through interleaved rows, and rows keep printing past
     * ten while they tie the tenth score.
     *
     * The two legacy pages diverge in one edge case: when a
     * supplemental row lands at the cutoff, recordseason.php stops the
     * whole list (`break 2`) while recordsweek.php only stops
     * interleaving and still prints the current DB row.
     * $stopListAtSupplementalCutoff selects the variant.
     *
     * @param array<array{pts: int}> $players DB rows, best first
     * @param array<array{pts: int}> $extras supplemental rows, best first
     * @return array<int, array> rows with 'rank' added
     */
    public function mergeRankedList(array $players, array $extras, bool $stopListAtSupplementalCutoff): array
    {
        $out = [];
        $count = 0;
        $limitScore = 0;
        $extraCount = 0;

        foreach ($players as $player) {
            $count++;
            if ($count == 10) {
                $limitScore = $player['pts'];
            } elseif ($count > 10 && $player['pts'] < $limitScore) {
                break;
            }

            while (isset($extras[$extraCount]) && $extras[$extraCount]['pts'] >= $player['pts']) {
                $out[] = ['rank' => $count] + $extras[$extraCount];
                $extraCount++;
                $count++;

                if ($count >= 10) {
                    $limitScore = $extras[$extraCount - 1]['pts'];
                    if ($player['pts'] < $limitScore) {
                        if ($stopListAtSupplementalCutoff) {
                            if ($count != 10) {
                                return $out; // legacy `break 2`
                            }
                            // count == 10: recordseason.php keeps interleaving
                        } else {
                            break;
                        }
                    }
                }
            }

            $out[] = ['rank' => $count] + $player;
        }

        return $out;
    }

    /**
     * @return array{game: array, season: array} record entries per kind
     */
    public function getRecords(int $season): array
    {
        return $this->buildRecords($season, self::GAME_THRESHOLDS, self::SEASON_THRESHOLDS, 'players', 'pos');
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
