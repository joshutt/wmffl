<?php

namespace App\Service;

use WMFFL\Team;

class StandingsCalculatorService
{
    /**
     * Build Team objects from raw repository data and pre-compute SOV.
     * Ports StandingsController::buildTeamArray() with added SOV pre-computation.
     */
    public function buildTeamArray(array $teamData, array $gameData): array
    {
        $teamArray = [];

        foreach ($teamData as $row) {
            $t = new Team($row['team'], $row['division'], $row['teamid']);
            $t->divRecord = [$row['divwin'], $row['divlose'], $row['divtie']];
            $t->divPtsFor = $row['divpf'] ?? 0;
            $t->divPtsAgt = $row['divpa'] ?? 0;
            $teamArray[$row['teamid']] = $t;
        }

        foreach ($gameData as $row) {
            $teamArray[$row['teamid']]->addGame($row['oppid'], $row['ptsfor'], $row['ptsagt'], $row['oppdiv']);
        }

        // Pre-compute SOV for each team before sorting
        foreach ($teamArray as $team) {
            $team->sov = $this->calculateSov($team, $teamArray);
        }

        return array_values($teamArray);
    }

    /**
     * Sort teams in place using tiebreaker comparison logic.
     * Replaces usort($teams, 'WMFFL\Team::orderteam').
     */
    public function sortTeams(array &$teams): void
    {
        usort($teams, fn(Team $a, Team $b) => $this->compareTeams($a, $b));
    }

    /**
     * Compare two teams using tiebreaker hierarchy.
     * Ports Team::orderteam() but uses pre-computed $team->sov.
     */
    private function compareTeams(Team $a, Team $b): int
    {
        if ($a === $b) {
            return 0;
        }

        if ($a->division < $b->division) {
            return -1;
        } elseif ($a->division > $b->division) {
            return 1;
        }

        if ($a->getWinPCT() > $b->getWinPCT()) {
            return -1;
        } elseif ($a->getWinPCT() < $b->getWinPCT()) {
            return 1;
        }

        $h2h = ['wins' => 0, 'losses' => 0, 'ties' => 0, 'ptsFor' => 0, 'ptsAgt' => 0];
        foreach ($a->games as $game) {
            if ($game[0] == $b->teamid) {
                $pFor = $game[1];
                $pAgt = $game[2];
                if ($pFor > $pAgt) {
                    $h2h['wins']++;
                } elseif ($pFor < $pAgt) {
                    $h2h['losses']++;
                } else {
                    $h2h['ties']++;
                }
                $h2h['ptsFor'] += $pFor;
                $h2h['ptsAgt'] += $pAgt;
            }
        }

        if ($h2h['wins'] > $h2h['losses']) {
            return -1;
        } elseif ($h2h['wins'] < $h2h['losses']) {
            return 1;
        }

        if ($a->division == $b->division) {
            if ($a->getDivWinPCT() > $b->getDivWinPCT()) {
                return -1;
            } elseif ($a->getDivWinPCT() < $b->getDivWinPCT()) {
                return 1;
            }
        }

        if ($a->sov > $b->sov) {
            return -1;
        } elseif ($a->sov < $b->sov) {
            return 1;
        }

        if ($h2h['ptsFor'] > $h2h['ptsAgt']) {
            return -1;
        } elseif ($h2h['ptsFor'] < $h2h['ptsAgt']) {
            return 1;
        }

        if ($a->name > $b->name) {
            return 1;
        } elseif ($a->name < $b->name) {
            return -1;
        }

        return 0;
    }

    /**
     * Calculate Strength of Victory for a team.
     * Ports Team::getSOV() but takes $allTeams as an explicit parameter.
     *
     * @param Team[] $allTeams Keyed by teamid
     */
    private function calculateSov(Team $team, array $allTeams): float
    {
        $rec = [0, 0, 0];

        foreach ($team->games as $game) {
            $opponentId = $game[0];
            $teamRec = null;
            foreach ($allTeams as $t) {
                if ($t->teamid == $opponentId) {
                    $teamRec = $t->record;
                    break;
                }
            }
            if ($teamRec === null) {
                continue;
            }

            if ($game[1] > $game[2]) {
                // Win: add opponent's full record
                $rec[0] += $teamRec[0];
                $rec[1] += $teamRec[1];
                $rec[2] += $teamRec[2];
            } elseif ($game[1] == $game[2]) {
                // Tie: add half of opponent's record
                $rec[0] += $teamRec[0] / 2.0;
                $rec[1] += $teamRec[1] / 2.0;
                $rec[2] += $teamRec[2] / 2.0;
            }
            // Loss: opponent's record not counted
        }

        $gamePlay = $rec[0] + $rec[1] + $rec[2];
        if ($gamePlay == 0) {
            return 0.00;
        }

        return ($rec[0] + $rec[2] / 2.0) / $gamePlay;
    }
}
