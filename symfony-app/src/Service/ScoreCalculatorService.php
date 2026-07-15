<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class ScoreCalculatorService
{
    public function __construct(private readonly Connection $conn) {}

    public function recalculateWeek(int $season, int $week): array
    {
        $games = $this->conn->fetchAllAssociative(
            'SELECT s.teama, s.teamb, ta.name AS teamaName, tb.name AS teambName
             FROM schedule s
             JOIN team ta ON ta.teamid = s.teama
             JOIN team tb ON tb.teamid = s.teamb
             WHERE s.season = :season AND s.week = :week',
            ['season' => $season, 'week' => $week]
        );

        $results = [];
        foreach ($games as $game) {
            $aPts = $this->scoreTeam($game['teama'], $season, $week);
            $bPts = $this->scoreTeam($game['teamb'], $season, $week);

            $aFinal = max(0, $aPts['off'] - $bPts['def'] - $aPts['penalty']);
            $bFinal = max(0, $bPts['off'] - $aPts['def'] - $bPts['penalty']);

            $this->conn->executeStatement(
                'UPDATE schedule SET scorea = :scorea, scoreb = :scoreb
                 WHERE season = :season AND week = :week AND teama = :teama AND teamb = :teamb',
                [
                    'scorea' => $aFinal,
                    'scoreb' => $bFinal,
                    'season' => $season,
                    'week'   => $week,
                    'teama'  => $game['teama'],
                    'teamb'  => $game['teamb'],
                ]
            );

            $results[] = [
                'teamaName' => $game['teamaName'],
                'teambName' => $game['teambName'],
                'scorea'    => $aFinal,
                'scoreb'    => $bFinal,
            ];
        }

        return $results;
    }

    private function scoreTeam(int $teamId, int $season, int $week): array
    {
        $rows = $this->conn->fetchAllAssociative(
            'SELECT p.pos, r.teamid, g.kickoff, g.secRemain, s.*,
             IF(r.dateon IS NULL AND p.pos <> \'HC\', 1, 0) AS illegal
             FROM newplayers p
             JOIN activations a ON p.playerid = a.playerid
             JOIN weekmap wm ON a.season = wm.season AND a.week = wm.week
             LEFT JOIN roster r ON p.playerid = r.playerid
               AND (r.dateoff IS NULL OR r.dateoff >= wm.activationdue)
               AND r.teamid = a.teamid
             LEFT JOIN nflrosters nr ON nr.playerid = p.playerid AND nr.dateoff IS NULL
             LEFT JOIN nflgames g ON g.season = a.season AND g.week = a.week
               AND nr.nflteamid IN (g.homeTeam, g.roadTeam)
             LEFT JOIN stats s ON s.statid = p.flmid AND s.week = a.week AND s.season = a.season
             WHERE a.teamid = :teamid AND a.season = :season AND a.week = :week',
            ['teamid' => $teamId, 'season' => $season, 'week' => $week]
        );

        $off = 0;
        $def = 0;
        $penalty = 0;

        foreach ($rows as $row) {
            if ($row['illegal']) {
                $penalty += 2;
                continue;
            }

            if ($row['kickoff'] === null && $row['pos'] !== 'HC') {
                $penalty += 2;
                continue;
            }

            $pts = match ($row['pos']) {
                'HC'        => $this->scoreHC($row),
                'QB'        => $this->scoreQB($row),
                'RB', 'WR' => $this->scoreOffense($row),
                'TE'        => $this->scoreTE($row),
                'K'         => $this->scoreK($row),
                'OL'        => $this->scoreOL($row),
                'DL', 'LB', 'DB' => $this->scoreDefense($row),
                default     => 0,
            };

            if (in_array($row['pos'], ['DL', 'LB', 'DB'], true)) {
                $def += $pts;
            } else {
                $off += $pts;
            }
        }

        return ['off' => $off, 'def' => $def, 'penalty' => $penalty];
    }

    private function scoreHC(array $row): int
    {
        $pts = 0;
        if ($row['played'] > 0) {
            if ($row['ptdiff'] == 0) {
                $pts = 1;
            } elseif ($row['ptdiff'] > 0) {
                $pts = 3 + (int) floor($row['ptdiff'] / 10);
            }

            $penalties = $row['penalties'];
            $pts += match (true) {
                $penalties <= 3  =>  3,
                $penalties <= 6  =>  2,
                $penalties <= 8  =>  1,
                $penalties <= 10 =>  0,
                $penalties <= 12 => -1,
                $penalties <= 14 => -2,
                default          => -3,
            };
        }
        return $pts;
    }

    private function scoreQB(array $row): int
    {
        $pts = -($row['fum'] + $row['intthrow']) * 2;
        if ($row['yards'] >= 200) {
            $pts += (int) floor(($row['yards'] - 175) / 25);
        }
        $pts += $row['tds'] * 6;
        $pts += $row['2pt'] * 2;
        return $pts;
    }

    private function scoreOffense(array $row): int
    {
        $pts = -$row['fum'] * 2;
        if ($row['yards'] >= 70) {
            $pts += (int) floor(($row['yards'] - 60) / 10);
        }
        if ($row['rec'] >= 5) {
            $pts += $row['rec'] - 4;
        }
        $pts += $row['tds'] * 6;
        $pts += $row['2pt'] * 2;
        $pts += $row['specTD'] * 12;
        return $pts;
    }

    private function scoreTE(array $row): int
    {
        $pts = $this->scoreOffense($row);
        if ($row['rec'] >= 2 && $row['rec'] <= 6) {
            $pts += 1;
        }
        if ($row['rec'] == 4) {
            $pts += 1;
        }
        if ($row['rec'] > 12) {
            $pts += $row['rec'] - 12;
        }
        return $pts;
    }

    private function scoreK(array $row): int
    {
        $pts  = $row['XP'];
        $pts -= $row['MissXP'];
        $pts += $row['2pt'] * 2;
        $pts += $row['FG30'] * 3;
        $pts += $row['FG40'] * 4;
        $pts += $row['FG50'] * 5;
        $pts += $row['FG60'] * 7;
        $pts -= $row['MissFG30'];
        $pts += $row['specTD'] * 12;
        return $pts;
    }

    private function scoreOL(array $row): int
    {
        $pts = $row['tds'];
        if ($row['yards'] >= 100) {
            $pts += (int) floor($row['yards'] / 10 - 9);
        }
        if ($row['played']) {
            $pts += match (true) {
                $row['sacks'] === 0 =>  5,
                $row['sacks'] === 1 =>  2,
                $row['sacks'] === 2 =>  1,
                $row['sacks'] === 5 => -1,
                $row['sacks'] === 6 => -2,
                $row['sacks'] === 7 => -5,
                $row['sacks'] >= 8  => -($row['sacks'] - 6) * 5,
                default             =>  0,
            };
        }
        return $pts;
    }

    private function scoreDefense(array $row): int
    {
        $pts  = $row['tackles'];
        $pts += (int) floor($row['sacks'] * 2);
        if ($row['sacks'] >= 3) {
            $pts += (int) floor($row['sacks'] - 2);
        }
        $pts += $row['intcatch'] * 4;
        $pts += $row['passdefend'];
        $pts += $row['fumrec'] * 2;
        $pts += $row['forcefum'] * 3;
        if ($row['returnyards'] > 0) {
            $pts += (int) floor($row['returnyards'] / 20);
        }
        $pts += $row['tds'] * 9;
        $pts += $row['2pt'] * 2;
        $pts += $row['specTD'] * 12;
        $pts += $row['Safety'] * 6;
        $pts += $row['blockpunt'] * 3;
        $pts += $row['blockxp'] * 3;
        $pts += $row['blockfg'] * 3;
        return $pts;
    }
}
