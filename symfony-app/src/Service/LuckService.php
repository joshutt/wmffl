<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Schedule-luck ratings, ported from football/stats/luck.php: what a
 * team's record would be had it played every other team every week
 * (using revised-activation scores), compared with its actual record.
 * Positive luck means the schedule flattered the record.
 */
class LuckService
{
    public function __construct(
        private Connection $connection,
        private SeasonRuleService $seasonRules
    ) {
    }

    /**
     * @return array{luck: array<string, float>, week: int, statSig: float}
     *   luck: team => rating (in percentage points), sorted luckiest first
     */
    public function getLuckRatings(int $season): array
    {
        $potential = $this->getPotentialScores($season);
        [$potentialRecords, $week] = $this->computePotentialRecords($potential);
        $actualRecords = $this->computeActualRecords($this->getActualScores($season, $week));

        $luck = [];
        foreach ($potentialRecords as $team => $record) {
            $games = $record['win'] + $record['lose'] + $record['tie'];
            $potentialPct = $games > 0 ? ($record['win'] + $record['tie'] / 2.0) / $games : 0.0;

            $actual = $actualRecords[$team] ?? ['win' => 0, 'lose' => 0, 'tie' => 0];
            $actualGames = $actual['win'] + $actual['lose'] + $actual['tie'];
            $actualPct = $actualGames > 0 ? ($actual['win'] + $actual['tie'] / 2.0) / $actualGames : 0.0;

            $luck[$team] = ($actualPct - $potentialPct) * 100;
        }
        arsort($luck);

        return [
            'luck' => $luck,
            'week' => $week,
            'statSig' => $week > 0 ? 100.0 / $week : 100.0,
        ];
    }

    /** Revised-activation offense/defense totals per team-week */
    public function getPotentialScores(int $season): array
    {
        return $this->connection->fetchAllAssociative(
            "select t.name, ps.week,
             sum(if(p.pos in ('HC', 'QB', 'RB', 'WR', 'TE', 'K', 'OL'), ps.active, 0)) as off,
             sum(if(p.pos in ('DL', 'LB', 'DB'), ps.active, 0)) as def
             from activations a
             JOIN playerscores ps ON a.season=ps.season and a.week=ps.week and a.playerid=ps.playerid
             JOIN players p ON p.playerid=ps.playerid
             JOIN teamnames t ON a.teamid=t.teamid and a.season=t.season
             where a.season = :season
             and a.week <= :regWeeks
             group by t.teamid, ps.week
             order by ps.week, t.name",
            ['season' => $season, 'regWeeks' => $this->seasonRules->getRegularSeasonWeeks($season)]
        );
    }

    /**
     * Round-robin every team against every other team each week: my
     * offense minus your defense vs. your offense minus my defense.
     *
     * @return array{0: array<string, array{win: int, lose: int, tie: int}>, 1: int} records and max week
     */
    public function computePotentialRecords(array $rows): array
    {
        $byWeek = [];
        foreach ($rows as $row) {
            $byWeek[(int) $row['week']][$row['name']] = ['off' => $row['off'], 'def' => $row['def']];
        }

        $records = [];
        $maxWeek = 0;
        foreach ($byWeek as $week => $scores) {
            $maxWeek = max($maxWeek, $week);
            foreach ($scores as $team => $teamScore) {
                $records[$team] ??= ['win' => 0, 'lose' => 0, 'tie' => 0];
                foreach ($scores as $opponent => $oppScore) {
                    if ($team === $opponent) {
                        continue;
                    }
                    $mine = $teamScore['off'] - $oppScore['def'];
                    $theirs = $oppScore['off'] - $teamScore['def'];
                    match ($mine <=> $theirs) {
                        0 => $records[$team]['tie']++,
                        -1 => $records[$team]['lose']++,
                        1 => $records[$team]['win']++,
                    };
                }
            }
        }

        return [$records, $maxWeek];
    }

    /** Real game results through the given week */
    public function getActualScores(int $season, int $week): array
    {
        return $this->connection->fetchAllAssociative(
            "select tn1.name, s.week,
             if (t1.teamid=s.teama, s.scorea, s.scoreb) as ptsfor,
             if (t1.teamid=s.teamb, s.scorea, s.scoreb) as ptsag
             from team t1, team t2, schedule s, teamnames tn1
             where s.season = :season and s.week <= :week
             and s.week <= :regWeeks
             and t1.teamid in (s.teama, s.teamb)
             and t2.teamid in (s.teama, s.teamb) and t1.teamid<>t2.teamid
             and tn1.teamid=t1.teamid and tn1.season=s.season
             order by s.week, tn1.name",
            ['season' => $season, 'week' => $week, 'regWeeks' => $this->seasonRules->getRegularSeasonWeeks($season)]
        );
    }

    /** @return array<string, array{win: int, lose: int, tie: int}> */
    public function computeActualRecords(array $rows): array
    {
        $records = [];
        foreach ($rows as $row) {
            $records[$row['name']] ??= ['win' => 0, 'lose' => 0, 'tie' => 0];
            if ($row['ptsfor'] > $row['ptsag']) {
                $records[$row['name']]['win']++;
            } elseif ($row['ptsfor'] < $row['ptsag']) {
                $records[$row['name']]['lose']++;
            } else {
                $records[$row['name']]['tie']++;
            }
        }

        return $records;
    }
}
