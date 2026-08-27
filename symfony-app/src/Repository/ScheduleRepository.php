<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Repository for fetching schedule data.
 * Ports the two queries from football/history/common/schedule.php.
 */
class ScheduleRepository
{
    /**
     * nflgames stores each game under whatever code the franchise used
     * in that season (e.g. Oakland's 2008-2019 games are still "OAK"),
     * but nflteams only keeps today's code per franchise ("LV"). Without
     * translating through a relocation, a moved team's current code
     * never matches its own historical games, so every week before the
     * move looks like a bye. Maps each retired code to the current code
     * nflteams uses for that franchise; extend this when a team next
     * relocates.
     */
    private const RELOCATED_CODES = [
        'OAK' => 'LV',  // Oakland -> Las Vegas Raiders, 2020
        'SD' => 'LAC',  // San Diego -> Los Angeles Chargers, 2017
        'STL' => 'LAR', // St. Louis -> Los Angeles Rams, 2016
        'LA' => 'LAR',  // Rams played under the bare "LA" code in 2016 only
    ];

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Get the season's matchups, joined to weekmap for week metadata and
     * teamnames for the season-correct team names.
     *
     * Ports the matchup query from schedule.php lines 8-18.
     *
     * @return array Array of matchup rows
     */
    public function getSeasonSchedule(int $season): array
    {
        $query = <<<EOD
SELECT s.week AS week,
    t1.name AS teama_name, s.teama AS teama_id, s.scorea AS scorea,
    t2.name AS teamb_name, s.teamb AS teamb_id, s.scoreb AS scoreb,
    w.weekname AS weekname, w.displayDate AS displayDate, w.enddate AS endDate,
    s.label AS label, s.postseason AS postseason
FROM schedule s
JOIN weekmap w ON s.season = w.season AND s.week = w.week
LEFT JOIN teamnames t1 ON s.teama = t1.teamid AND s.season = t1.season
LEFT JOIN teamnames t2 ON s.teamb = t2.teamid AND s.season = t2.season
WHERE s.season = :season
ORDER BY s.week, s.label, MD5(CONCAT(t1.name, t2.name))
EOD;

        return $this->connection->fetchAllAssociative($query, ['season' => $season]);
    }

    /**
     * Get the NFL teams on bye for each week of the season.
     *
     * Ports the bye-week query from schedule.php lines 20-28, with two
     * additions:
     * - The legacy query's LEFT JOIN nflgames has no season-level guard,
     *   so for a season with zero nflgames rows every team matches as
     *   "on bye" every week instead of the intended empty result.
     *   nflgames only goes back to 2008 (not 2000 as assumed when this
     *   was ported), so this bites 2000-2007 as well as 1992-1999
     *   without the EXISTS guard below.
     * - Relocated franchises (see RELOCATED_CODES) are translated to
     *   their current code before matching nflgames, so a team's
     *   pre-move seasons aren't misread as all-bye weeks.
     *
     * @return array Array of {nflteam, name, nickname, week} rows
     */
    public function getByeWeeks(int $season): array
    {
        $homeCode = $this->currentCodeExpression('g.homeTeam');
        $roadCode = $this->currentCodeExpression('g.roadTeam');

        $query = <<<EOD
SELECT t.nflteam AS nflteam, t.name AS name, t.nickname AS nickname, wm.week AS week
FROM nflteams t
JOIN weekmap wm
LEFT JOIN nflgames g ON g.season = wm.season AND g.week = wm.week
    AND t.nflteam IN ({$homeCode}, {$roadCode})
WHERE wm.season = :season AND g.week IS NULL AND wm.week > 0
AND EXISTS (SELECT 1 FROM nflgames WHERE season = :season)
GROUP BY wm.week, t.nflteam
ORDER BY wm.week, t.name
EOD;

        return $this->connection->fetchAllAssociative($query, ['season' => $season]);
    }

    /**
     * Builds a SQL CASE expression translating a retired nflgames team
     * code to the current code nflteams uses, per RELOCATED_CODES.
     * RELOCATED_CODES is a fixed internal constant, never user input,
     * so inlining its values is safe.
     */
    private function currentCodeExpression(string $column): string
    {
        $whens = '';
        foreach (self::RELOCATED_CODES as $retired => $current) {
            $whens .= "WHEN '{$retired}' THEN '{$current}' ";
        }

        return "CASE {$column} {$whens}ELSE {$column} END";
    }

    /**
     * Whether the schedule table has any rows for the given season.
     * Used by ScheduleService::resolveDefaultSeason() to decide whether
     * the current season's schedule is populated yet.
     */
    public function hasRows(int $season): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM schedule WHERE season = :season LIMIT 1',
            ['season' => $season]
        );
    }
}
