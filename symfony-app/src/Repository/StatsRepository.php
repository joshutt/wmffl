<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Queries for the stats pages, ported from football/stats/
 * {leaders,playerstats,statcsv,playerlist}.php with bound parameters.
 */
class StatsRepository
{
    /** Per-position stat columns (legacy $posMap), alias => expression */
    public const POS_MAP = [
        'QB' => ['yards' => 's.yards', 'tds' => 's.tds', 'intthrow' => 's.intthrow', 'fum' => 's.fum', 'twopt' => 's.2pt'],
        'RB' => ['yards' => 's.yards', 'rec' => 's.rec', 'tds' => 's.tds', 'fum' => 's.fum', 'twopt' => 's.2pt', 'spectd' => 's.specTD'],
        'WR' => ['yards' => 's.yards', 'rec' => 's.rec', 'tds' => 's.tds', 'fum' => 's.fum', 'twopt' => 's.2pt', 'spectd' => 's.specTD'],
        'TE' => ['yards' => 's.yards', 'rec' => 's.rec', 'tds' => 's.tds', 'fum' => 's.fum', 'twopt' => 's.2pt', 'spectd' => 's.specTD'],
        'K' => ['xp' => 's.XP', 'missxp' => 's.MissXP', 'fg30' => 's.FG30', 'fg40' => 's.FG40', 'fg50' => 's.FG50', 'fg60' => 's.FG60', 'missfg30' => 's.MissFG30', 'twopt' => 's.2pt', 'spectd' => 's.specTD'],
        'OL' => ['yards' => 's.yards', 'sacks' => 's.sacks', 'tds' => 's.tds'],
        'DL' => ['tackles' => 's.tackles', 'passdefend' => 's.passdefend', 'sacks' => 's.sacks', 'intcatch' => 's.intcatch', 'fumrec' => 's.fumrec', 'forcefum' => 's.forcefum', 'returnyards' => 's.returnyards', 'safety' => 's.Safety', 'tds' => 's.tds', 'spectd' => 's.specTD'],
        'LB' => ['tackles' => 's.tackles', 'passdefend' => 's.passdefend', 'sacks' => 's.sacks', 'intcatch' => 's.intcatch', 'fumrec' => 's.fumrec', 'forcefum' => 's.forcefum', 'returnyards' => 's.returnyards', 'safety' => 's.Safety', 'tds' => 's.tds', 'spectd' => 's.specTD'],
        'DB' => ['tackles' => 's.tackles', 'passdefend' => 's.passdefend', 'sacks' => 's.sacks', 'intcatch' => 's.intcatch', 'fumrec' => 's.fumrec', 'forcefum' => 's.forcefum', 'returnyards' => 's.returnyards', 'safety' => 's.Safety', 'tds' => 's.tds', 'spectd' => 's.specTD'],
        'HC' => ['wins' => 'if(s.ptdiff>0,1,0)', 'ptdiff' => 's.ptdiff', 'penalties' => 's.penalties'],
    ];

    public const POS_LABELS = [
        'QB' => ['Yards', 'TDs', 'INT', 'Fumbles', '2pt'],
        'RB' => ['Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'],
        'WR' => ['Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'],
        'TE' => ['Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'],
        'K' => ['XP', 'Miss XP', 'FG 0-39', 'FG 40-49', 'FG 50-59', 'FG 60+', 'Miss FG 0-30', '2pt', 'Special TDs'],
        'OL' => ['Yards', 'Sacks', 'TDs'],
        'DL' => ['T', 'PD', 'Sck', 'INT', 'FR', 'FF', 'Ret Yds', 'Safety', 'TDs', 'Spec TDs'],
        'LB' => ['T', 'PD', 'Sck', 'INT', 'FR', 'FF', 'Ret Yds', 'Safety', 'TDs', 'Spec TDs'],
        'DB' => ['T', 'PD', 'Sck', 'INT', 'FR', 'FF', 'Ret Yds', 'Safety', 'TDs', 'Spec TDs'],
        'HC' => ['Wins', 'Pt Diff', 'Pen'],
    ];

    public const POSITIONS = ['HC', 'QB', 'RB', 'WR', 'TE', 'K', 'OL', 'DL', 'LB', 'DB'];

    private const OFFENSE = ['HC', 'QB', 'RB', 'WR', 'TE', 'K', 'OL'];
    private const DEFENSE = ['DL', 'LB', 'DB'];

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Team points by position (leaders.php): one row per team with a
     * column per position plus offense/defense/total sums.
     */
    public function getLeaders(int $season): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT t.name, p.pos, sum(ps.active) as totpts
             FROM playerscores ps
                 JOIN players p ON ps.playerid=p.playerid
                 JOIN roster r ON r.PlayerID=p.playerid
                 JOIN teamnames t ON r.teamid=t.teamid and ps.season=t.season
                 JOIN weekmap w ON r.dateon <= w.activationdue and (r.dateoff is null or r.dateoff > w.activationdue)
             WHERE w.season = :season and ps.season=w.season and ps.week=w.week
             and ps.week<=14
             and ps.active is not null
             GROUP BY t.name, p.pos
             ORDER BY t.name, p.pos",
            ['season' => $season]
        );

        $teams = [];
        foreach ($rows as $row) {
            if (!isset($teams[$row['name']])) {
                $teams[$row['name']] = ['name' => $row['name']] + array_fill_keys(self::POSITIONS, 0);
            }
            $teams[$row['name']][$row['pos']] = $row['totpts'];
        }

        foreach ($teams as $name => $team) {
            $offense = $defense = 0;
            foreach (self::OFFENSE as $pos) {
                $offense += $team[$pos];
            }
            foreach (self::DEFENSE as $pos) {
                $defense += $team[$pos];
            }
            $teams[$name] += ['offense' => $offense, 'defense' => $defense, 'total' => $offense + $defense];
        }

        return array_values($teams);
    }

    /** Latest week with scores on file, capped (leaders/playerrecord $dateQuery) */
    public function getMaxScoredWeek(int $season, int $cap = 14): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT max(week) FROM playerscores where season = :season and week <= :cap',
            ['season' => $season, 'cap' => $cap]
        );
    }

    /**
     * Per-player season stat lines for one position (shared base of
     * playerstats.php and statcsv.php). Row shape: playerid, name, team
     * (NFL), bye, ffteam, games, pts, ppg plus the POS_MAP aliases.
     *
     * @param bool $hcPenalties statcsv's HC export omitted the penalty column
     */
    public function getPlayerStats(
        int $season,
        string $pos,
        string $sort = 'ppg',
        int $startWeek = 1,
        int $endWeek = 17,
        bool $hcPenalties = true
    ): array {
        $statColumns = self::POS_MAP[$pos] ?? self::POS_MAP['QB'];
        if (!isset(self::POS_MAP[$pos])) {
            $pos = 'QB';
        }
        if ($pos === 'HC' && !$hcPenalties) {
            unset($statColumns['penalties']);
        }

        $statSelect = '';
        foreach ($statColumns as $alias => $expr) {
            $statSelect .= "sum($expr) as `$alias`, ";
        }

        // ORDER BY can't be parameterized; anything outside the output
        // columns falls back to the ppg default
        $sortColumn = in_array($sort, array_merge(['name', 'games', 'pts', 'ppg'], array_keys($statColumns)), true)
            ? $sort : 'ppg';

        $rows = $this->connection->fetchAllAssociative(
            "SELECT p.playerid, CONCAT(p.firstname, ' ', p.lastname) as name, p.pos, p.team, b.week as bye,
             t.abbrev as ffteam,
             sum(if(s.played>0,1,0)) as games,
             sum(ps.pts) as pts,
             $statSelect
             round(sum(ps.pts)/sum(if(s.played>0,1,0)), 2) as ppg
             FROM playerscores ps
             JOIN players p ON ps.playerid=p.playerid
             JOIN stats s ON s.statid=p.flmid AND s.season=ps.season AND s.week=ps.week
             LEFT JOIN roster r ON r.playerid=p.playerid AND r.dateoff is null
             LEFT JOIN team t ON t.teamid=r.teamid
             LEFT JOIN nflbyes b ON p.team=b.nflteam AND ps.season=b.season
             WHERE ps.season = :season
             AND p.pos = :pos
             AND p.usepos=1
             AND ps.week >= :startWeek AND ps.week <= :endWeek
             GROUP BY p.playerid
             ORDER BY `$sortColumn` DESC, `pts` DESC",
            ['season' => $season, 'pos' => $pos, 'startWeek' => $startWeek, 'endWeek' => $endWeek]
        );

        // Legacy recomputed PPG with a games floor of one so scoreless
        // players don't divide by zero
        foreach ($rows as &$row) {
            $row['ppg'] = round($row['pts'] / max(1, (int) $row['games']), 2);
        }

        return $rows;
    }

    /**
     * Every active player's weekly scores for the season
     * (playerlist.php; its query referenced columns dropped from
     * players — status/position — so the port keys off active/pos).
     */
    public function getActivePlayerScores(int $season): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT p.lastname, p.firstname, p.pos, p.team, ps.week, ps.pts
             FROM playerscores ps, players p
             WHERE ps.season = :season AND ps.playerid = p.playerid AND p.active=1
             ORDER BY ps.week, p.pos, ps.pts DESC, p.lastname, p.firstname',
            ['season' => $season]
        );
    }
}
