<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/toomanplayers')]
class AdminTooManyPlayersController extends AbstractAdminController
{
    private const ROSTER_LIMIT = 26;

    #[Route('/{season}', name: 'admin_toomanplayers', defaults: ['season' => null])]
    public function index(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        ?int $season = null
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season ??= $seasonWeek->getCurrentSeason();

        $rows = $em->getConnection()->fetchAllAssociative(
            "SELECT wm.weekname, t.name, COUNT(r.playerid) AS playerCount, wm.week
             FROM team t
             JOIN roster r ON t.teamid = r.teamid
             JOIN weekmap wm
               ON (r.dateoff IS NULL OR r.dateoff > wm.activationdue)
              AND r.dateon < wm.activationdue
             WHERE wm.season = :season
               AND NOW() >= wm.startdate
               AND wm.week >= 1
             GROUP BY wm.week, wm.weekname, t.teamid, t.name
             HAVING COUNT(r.playerid) > :limit
             ORDER BY COUNT(r.playerid) DESC",
            ['season' => $season, 'limit' => self::ROSTER_LIMIT]
        );

        return $this->render('admin/toomanplayers/index.html.twig', [
            'season' => $season,
            'rows'   => $rows,
            'limit'  => self::ROSTER_LIMIT,
        ]);
    }
}
