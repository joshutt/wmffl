<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Power rankings from potential vs. actual points, ported from
 * football/stats/powerrate.php (powerlist.php was the same math dumped
 * as debug output).
 *
 * Potential points take each team's best weekly lineup: the top scorer
 * at every position, the second WR/DL/LB/DB, and the best flex among
 * RB2/WR3/TE2. The rating blends a sqrt(week)-weighted and a flat
 * average of (potential + 2*actual)/3, favoring recent weeks.
 */
class PowerRatingService
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @return array{ratings: array<string, array<int, float>>, week: int}
     *   ratings: team => week => rating, sorted best-first by latest weeks
     */
    public function getPowerRatings(int $season): array
    {
        [$potential, $actual] = $this->computeWeeklyPoints($this->getWeeklyScores($season));

        $week = 0;
        foreach ($potential as $weeks) {
            $week = max($week, ...array_keys($weeks));
        }

        $ratings = $this->computeRatings($potential, $actual);
        uasort($ratings, self::compareRatings(...));

        return ['ratings' => $ratings, 'week' => $week];
    }

    /** Next week's matchups with spreads from the rating gap (half-point rounding) */
    public function getLines(int $season, int $completedWeek, array $ratings): array
    {
        $games = $this->connection->fetchAllAssociative(
            'SELECT t1.name as teama, t2.name as teamb FROM schedule s, team t1, team t2
             WHERE s.teama=t1.teamid AND s.teamb=t2.teamid AND s.season = :season AND s.week = :week',
            ['season' => $season, 'week' => $completedWeek + 1]
        );

        $lines = [];
        foreach ($games as $game) {
            $ratingA = $ratings[$game['teama']][$completedWeek] ?? 0;
            $ratingB = $ratings[$game['teamb']][$completedWeek] ?? 0;
            [$favorite, $underdog] = $ratingA > $ratingB
                ? [$game['teama'], $game['teamb']]
                : [$game['teamb'], $game['teama']];

            $lines[] = [
                'favorite' => $favorite,
                'underdog' => $underdog,
                'line' => round(abs($ratingA - $ratingB) * 2) / 2,
            ];
        }

        return $lines;
    }

    /** Rostered players' weekly scores for the completed weeks of a season */
    public function getWeeklyScores(int $season): array
    {
        return $this->connection->fetchAllAssociative(
            'select wm.week, t.name, p.firstname, p.lastname, p.pos, ps.pts, ps.active
             from roster r
             join weekmap wm on r.DateOn <= wm.ActivationDue and (r.DateOff is null or r.DateOff >= wm.ActivationDue)
             join players p on r.PlayerID=p.playerid
             join teamnames t on r.TeamID=t.teamid and wm.Season=t.season
             join playerscores ps on ps.playerid=p.playerid and ps.season=wm.Season and ps.week=wm.Week
             where wm.Season = :season and wm.EndDate < now()
             order by wm.week, t.name, p.pos, ps.pts desc',
            ['season' => $season]
        );
    }

    /**
     * Accumulate potential and actual points per team-week. Rows must be
     * ordered by week, team, pos, pts desc (as getWeeklyScores returns).
     *
     * @return array{0: array<string, array<int, float>>, 1: array<string, array<int, float>>}
     */
    public function computeWeeklyPoints(array $rows): array
    {
        $potential = [];
        $actual = [];

        $curTeam = null;
        $curPos = null;
        $count = 0;
        $ppt = $apt = 0;
        $bestFlex = 0;

        foreach ($rows as $row) {
            $week = (int) $row['week'];
            $team = $row['name'];
            if ($curTeam !== $team) {
                $curPos = null;
                $curTeam = $team;
                $count = 0;
                $potential[$team] ??= [];
                $actual[$team] ??= [];
                $ppt = 0;
                $apt = 0;
                $bestFlex = 0;
            }

            $pts = $row['pts'];
            $apt += $row['active'];

            // Best player at each position always counts
            if ($curPos !== $row['pos']) {
                $ppt += $pts;
                $curPos = $row['pos'];
                $count = 0;
            }

            // Second WR/DL/LB/DB counts too
            if (in_array($curPos, ['WR', 'DL', 'LB', 'DB'], true) && $count === 1) {
                $ppt += $pts;
            }

            // One flex spot goes to the best of RB2 / WR3 / TE2
            if (($curPos === 'RB' && $count === 1) || ($curPos === 'WR' && $count === 2) || ($curPos === 'TE' && $count === 1)) {
                if ($pts > $bestFlex) {
                    $ppt -= $bestFlex;
                    $ppt += $pts;
                    $bestFlex = $pts;
                }
            }
            $count++;

            $potential[$team][$week] = $ppt;
            $actual[$team][$week] = $apt;
        }

        return [$potential, $actual];
    }

    /**
     * Per-week power ratings: average of a sqrt(week)-weighted and a
     * flat cumulative (potential + 2*actual)/3.
     *
     * @return array<string, array<int, float>> team => week => rating
     */
    public function computeRatings(array $potential, array $actual): array
    {
        $ratings = [];
        foreach ($potential as $team => $weeks) {
            $weight = 0.0;
            $totPot = $totAct = 0;
            $weighPot = $weighAct = 0.0;
            $teamRatings = [];
            foreach ($weeks as $week => $pot) {
                $act = $actual[$team][$week];
                $newWeight = sqrt($week);
                $weight += $newWeight;
                $totPot += $pot;
                $totAct += $act;
                $weighPot += $newWeight * $pot;
                $weighAct += $newWeight * $act;

                $weighted = ($weighPot + 2.0 * $weighAct) / (3.0 * $weight);
                $flat = ($totPot + 2.0 * $totAct) / (3.0 * $week);
                $teamRatings[$week] = ($weighted + $flat) / 2.0;
            }
            $ratings[$team] = $teamRatings;
        }

        return $ratings;
    }

    /** Latest weeks first, higher rating first (legacy powersort) */
    private static function compareRatings(array $a, array $b): int
    {
        $aReversed = array_values(array_reverse($a));
        $bReversed = array_values(array_reverse($b));
        for ($i = 0; $i < count($aReversed); $i++) {
            $comparison = ($bReversed[$i] ?? 0) <=> ($aReversed[$i] ?? 0);
            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }
}
