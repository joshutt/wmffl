<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use App\Service\MvpScoringService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/weekly')]
class AdminWeeklyController extends AbstractAdminController
{
    #[Route('/{season}/{week}', name: 'admin_weekly', defaults: ['season' => null, 'week' => null])]
    public function index(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        MvpScoringService $scoring,
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
            FROM activations a
            JOIN playerscores ps ON a.week = ps.week AND a.season = ps.season AND a.playerid = ps.playerid
            JOIN schedule s ON s.season = a.season AND s.week = a.week AND a.teamid IN (s.teama, s.teamb)
            JOIN players p ON p.playerid = a.playerid
            JOIN teamnames tn ON tn.teamid = a.teamid AND tn.season = a.season
            WHERE a.season = :season AND a.week = :week
            ORDER BY s.gameid, a.pos, a.teamid
            SQL,
            ['season' => $season, 'week' => $week]
        );

        $allPlayers = $scoring->rankPlayers($rows);
        $defensivePositions = ['DL', 'LB', 'DB'];

        return $this->render('admin/weekly/index.html.twig', [
            'season'  => $season,
            'week'    => $week,
            'overall' => array_slice($allPlayers, 0, 10),
            'defense' => array_slice(
                array_values(array_filter($allPlayers, fn($p) => in_array($p['pos'], $defensivePositions))),
                0, 10
            ),
        ]);
    }
}
