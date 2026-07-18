<?php

namespace App\Service;

use App\Model\ScoringRules;
use Doctrine\DBAL\Connection;

class ScoreCalculatorService
{
    public function __construct(
        private readonly Connection $conn,
        private readonly SeasonRuleService $seasonRules,
        private readonly PlayerScorerService $scorer,
    ) {}

    public function recalculateWeek(int $season, int $week): array
    {
        $rules = $this->seasonRules->getScoringRules($season);
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
            $aPts = $this->scoreTeam($game['teama'], $season, $week, $rules);
            $bPts = $this->scoreTeam($game['teamb'], $season, $week, $rules);

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

    private function scoreTeam(int $teamId, int $season, int $week, ScoringRules $rules): array
    {
        $rows = $this->conn->fetchAllAssociative(
            'SELECT p.pos, r.teamid, g.kickoff, g.secRemain, s.*,
             IF(r.dateon IS NULL AND p.pos <> \'HC\', 1, 0) AS illegal
             FROM players p
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

        $illegalPenalty = $rules->int('illegal_lineup_penalty');
        foreach ($rows as $row) {
            if ($row['illegal']) {
                $penalty += $illegalPenalty;
                continue;
            }

            if ($row['kickoff'] === null && $row['pos'] !== 'HC') {
                $penalty += $illegalPenalty;
                continue;
            }

            $pts = $this->scorer->total($row['pos'], $row, $rules);

            if (in_array($row['pos'], ['DL', 'LB', 'DB'], true)) {
                $def += $pts;
            } else {
                $off += $pts;
            }
        }

        return ['off' => $off, 'def' => $def, 'penalty' => $penalty];
    }
}
