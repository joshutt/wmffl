<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/draftdates')]
class AdminDraftDatesController extends AbstractAdminController
{
    #[Route('/{season}', name: 'admin_draftdates', defaults: ['season' => null])]
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
        $conn     = $em->getConnection();

        $rows = $conn->fetchAllAssociative(
            "SELECT t.name, d.Date AS date, MIN(d.Attend) AS attend
             FROM draftdate d
             JOIN user u ON u.UserID = d.UserID
             JOIN team t ON t.teamid = u.teamid
             WHERE d.Date > :cutoff
             GROUP BY u.teamid, t.name, d.Date
             ORDER BY d.Date",
            ['cutoff' => $season . '-01-01']
        );

        $dates  = [];
        $maxYes = 0;
        foreach ($rows as $row) {
            $date = $row['date'];
            if (!isset($dates[$date])) {
                $dates[$date] = ['yes' => 0, 'no' => 0];
            }
            if ($row['attend'] === 'Y') {
                $dates[$date]['yes']++;
            } else {
                $dates[$date]['no']++;
            }
            $maxYes = max($maxYes, $dates[$date]['yes']);
        }

        $noVote = $conn->fetchFirstColumn(
            "SELECT tn.name
             FROM owners o
             JOIN draftvote dv ON o.userid = dv.userid AND dv.season = o.season
             JOIN teamnames tn ON tn.season = o.season AND tn.teamid = o.teamid
             WHERE o.season = :season
             GROUP BY o.teamid, tn.name
             HAVING MAX(dv.lastUpdate) IS NULL",
            ['season' => $season]
        );

        return $this->render('admin/draftdates/index.html.twig', [
            'season' => $season,
            'dates'  => $dates,
            'maxYes' => $maxYes,
            'noVote' => $noVote,
        ]);
    }
}
