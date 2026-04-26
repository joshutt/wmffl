<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/mvp')]
class AdminMvpController extends AbstractAdminController
{
    #[Route('/{season}/{week}', name: 'admin_mvp', defaults: ['season' => null, 'week' => null])]
    public function index(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        ?int $season = null,
        ?int $week = null
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        if ($season === null || $week === null) {
            if ($seasonWeek->getCurrentWeek() > 0) {
                $season = $season ?? $seasonWeek->getCurrentSeason();
                $week   = $week   ?? max(1, $seasonWeek->getCurrentWeek() - 1);
            } else {
                $season = $season ?? $seasonWeek->getPreviousWeekSeason();
                $week   = $week   ?? $seasonWeek->getPreviousWeek();
            }
        }

        $rows = $em->getConnection()->fetchAllAssociative(
            <<<SQL
            SELECT a.playerid, a.pos, a.teamid,
                   IF(a.teamid = s.teama, s.teamb, s.teama) AS opp,
                   ps.active,
                   CONCAT(p.firstname, ' ', p.lastname) AS name,
                   tn.abbrev,
                   a.week
            FROM revisedactivations a
            JOIN playerscores ps ON a.week = ps.week AND a.season = ps.season AND a.playerid = ps.playerid
            JOIN schedule s ON s.season = a.season AND s.week = a.week AND a.teamid IN (s.teama, s.teamb)
            JOIN newplayers p ON p.playerid = a.playerid
            JOIN teamnames tn ON tn.teamid = a.teamid AND tn.season = a.season
            WHERE a.season = :season AND a.week <= :week AND a.week <= 14
            ORDER BY s.gameid, a.pos, a.teamid
            SQL,
            ['season' => $season, 'week' => $week]
        );

        $allPlayers  = $this->rankPlayers($rows);
        $defensivePositions = ['DL', 'LB', 'DB'];

        return $this->render('admin/mvp/index.html.twig', [
            'season'   => $season,
            'week'     => $week,
            'overall'  => array_slice($allPlayers, 0, 10),
            'defense'  => array_slice(
                array_values(array_filter($allPlayers, fn($p) => in_array($p['pos'], $defensivePositions))),
                0, 10
            ),
        ]);
    }

    private function rankPlayers(array $rows): array
    {
        $posVal    = [];
        $posCount  = [];
        $allPlayers = [];
        $fullPlayers = [];

        foreach ($rows as $row) {
            $pos    = $row['pos'];
            $teamid = (int) $row['teamid'];
            $week   = (int) $row['week'];
            $active = (float) $row['active'];

            if ($active >= 0) {
                $posVal[$pos][$teamid][$week] = ($posVal[$pos][$teamid][$week] ?? 0.0) + $active;
            }
            $posCount[$pos][$teamid][$week] = ($posCount[$pos][$teamid][$week] ?? 0) + 1;

            $pid = (int) $row['playerid'];
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

    private function compare(float $scoreA, float $scoreB): float
    {
        if ($scoreA < 0)          return $scoreA;
        if ($scoreA <= $scoreB)   return 0.0;
        if ($scoreB < 0)          return $scoreA;
        return $scoreA - $scoreB;
    }

    /**
     * Compare a flex-position player against the opponent, trying two groupings in order.
     * Falls back to $fallback if neither grouping matches.
     */
    private function flexCompare(
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
