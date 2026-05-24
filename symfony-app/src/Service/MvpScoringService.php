<?php

namespace App\Service;

class MvpScoringService
{
    /**
     * Given raw rows from the activations/scores query, compute a ranked player list.
     * Each row must have: playerid, pos, teamid, opp, active, name, abbrev, week.
     *
     * @return array<int, array{name: string, pos: string, abbrev: string, score: float}>
     */
    public function rankPlayers(array $rows): array
    {
        $posVal     = [];
        $posCount   = [];
        $allPlayers = [];
        $fullPlayers = [];

        foreach ($rows as $row) {
            $pos    = $row['pos'];
            $teamid = (int) $row['teamid'];
            $week   = (int) $row['week'];
            $active = (float) $row['active'];
            $pid    = (int) $row['playerid'];

            if ($active >= 0) {
                $posVal[$pos][$teamid][$week] = ($posVal[$pos][$teamid][$week] ?? 0.0) + $active;
            }
            $posCount[$pos][$teamid][$week] = ($posCount[$pos][$teamid][$week] ?? 0) + 1;

            $allPlayers[$pid][$week] = $row;
            if (!isset($fullPlayers[$pid]) || $week > (int) $fullPlayers[$pid]['week']) {
                $fullPlayers[$pid] = $row;
            }
        }

        $finals = [];

        foreach ($allPlayers as $playerId => $weeklyData) {
            foreach ($weeklyData as $week => $player) {
                $pos   = $player['pos'];
                $team  = (int) $player['teamid'];
                $opp   = (int) $player['opp'];
                $score = (float) $player['active'];
                $week  = (int) $week;

                $oppVal   = fn(string $p) => $posVal[$p][$opp][$week] ?? 0.0;
                $oppCount = fn(string $p) => $posCount[$p][$opp][$week] ?? 0;
                $myCount  = fn(string $p) => $posCount[$p][$team][$week] ?? 0;

                $val = match(true) {
                    in_array($pos, ['HC', 'QB', 'K', 'OL']) =>
                        $this->compare($score, $oppVal($pos)),

                    in_array($pos, ['DL', 'LB', 'DB']) =>
                        $this->compare($score, $oppVal($pos) / 2.0),

                    $myCount($pos) === $oppCount($pos) =>
                        $this->compare($score, $oppCount($pos) > 0 ? $oppVal($pos) / $oppCount($pos) : 0.0),

                    $pos === 'RB' => $this->flexCompare($score, $opp, $week, ['RB', 'TE'], ['RB', 'WR'], $team, $posVal, $posCount),
                    $pos === 'WR' => $this->flexCompare($score, $opp, $week, ['WR', 'TE'], ['WR'],       $team, $posVal, $posCount),
                    default       => $this->flexCompare($score, $opp, $week, ['RB', 'TE'], ['WR', 'TE'], $team, $posVal, $posCount, $oppVal('TE')),
                };

                $finals[$playerId] = ($finals[$playerId] ?? 0.0) + $val;
            }
        }

        arsort($finals);

        $result = [];
        foreach ($finals as $playerId => $score) {
            $result[] = [
                'name'   => $fullPlayers[$playerId]['name'],
                'pos'    => $fullPlayers[$playerId]['pos'],
                'abbrev' => $fullPlayers[$playerId]['abbrev'],
                'score'  => round($score, 2),
            ];
        }

        return $result;
    }

    public function compare(float $scoreA, float $scoreB): float
    {
        if ($scoreA < 0)        return $scoreA;
        if ($scoreA <= $scoreB) return 0.0;
        if ($scoreB < 0)        return $scoreA;
        return $scoreA - $scoreB;
    }

    /**
     * Compare a flex-position player against the opponent using two roster groupings in order.
     * Falls back to $fallback average if neither grouping count matches.
     */
    public function flexCompare(
        float $score,
        int $opp,
        int $week,
        array $primaryGroup,
        array $secondaryGroup,
        int $team,
        array $posVal,
        array $posCount,
        float $fallback = 0.0
    ): float {
        $oppGroupVal   = fn(array $pos) => array_sum(array_map(fn($p) => $posVal[$p][$opp][$week] ?? 0.0, $pos));
        $oppGroupCount = fn(array $pos) => array_sum(array_map(fn($p) => $posCount[$p][$opp][$week] ?? 0, $pos));
        $myGroupCount  = fn(array $pos) => array_sum(array_map(fn($p) => $posCount[$p][$team][$week] ?? 0, $pos));

        if ($myGroupCount($primaryGroup) === $oppGroupCount($primaryGroup)) {
            $cnt = $oppGroupCount($primaryGroup);
            return $this->compare($score, $cnt > 0 ? $oppGroupVal($primaryGroup) / $cnt : 0.0);
        }

        if ($myGroupCount($secondaryGroup) === $oppGroupCount($secondaryGroup)) {
            $cnt = $oppGroupCount($secondaryGroup);
            return $this->compare($score, $cnt > 0 ? $oppGroupVal($secondaryGroup) / $cnt : 0.0);
        }

        return $this->compare($score, $fallback);
    }
}
