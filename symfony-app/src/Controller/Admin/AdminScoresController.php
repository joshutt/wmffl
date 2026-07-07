<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use App\Service\ScoreCalculatorService;
use App\Service\SeasonWeekService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/scores')]
class AdminScoresController extends AbstractAdminController
{
    #[Route('', name: 'admin_scores')]
    public function index(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/scores/index.html.twig', [
            'currentSeason'  => $seasonWeek->getCurrentSeason(),
            'currentWeek'    => $seasonWeek->getCurrentWeek(),
            'previousSeason' => $seasonWeek->getPreviousWeekSeason(),
            'previousWeek'   => $seasonWeek->getPreviousWeek(),
            'results'        => null,
        ]);
    }

    #[Route('/reprocess', name: 'admin_scores_reprocess', methods: ['POST'])]
    public function reprocess(
        Request $request,
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        ScoreCalculatorService $calculator,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_scores');

        $season = (int) $request->request->get('season');
        $week   = (int) $request->request->get('week');

        $results = $calculator->recalculateWeek($season, $week);

        return $this->render('admin/scores/index.html.twig', [
            'currentSeason'   => $seasonWeek->getCurrentSeason(),
            'currentWeek'     => $seasonWeek->getCurrentWeek(),
            'previousSeason'  => $seasonWeek->getPreviousWeekSeason(),
            'previousWeek'    => $seasonWeek->getPreviousWeek(),
            'results'         => $results,
            'processedSeason' => $season,
            'processedWeek'   => $week,
        ]);
    }
}
