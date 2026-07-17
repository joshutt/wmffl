<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\DraftScheduleService;
use App\Service\SeasonWeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Member draft-date availability vote, ported from the per-season
 * football/history/{year}Season/draftdate.php pages and
 * history/common/processdraftdate.php. Default is Yes for every date;
 * a member may mark at most 4 dates "No", and a valid submission
 * stamps their draftvote row so the admin tally can chase stragglers.
 */
class DraftDateController extends AbstractController
{
    #[Route('/draftdate', name: 'draftdate', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        DraftScheduleService $schedule
    ): Response {
        $season = $seasonWeek->getCurrentSeason();

        if (!$auth->isLoggedIn()) {
            return $this->render('draftdate/index.html.twig', [
                'loggedIn' => false,
                'season'   => $season,
                'dates'    => [],
                'success'  => false,
                'error'    => false,
                'maxNo'    => DraftScheduleService::MAX_NO_VOTES,
            ]);
        }

        $userId = $auth->getUserId();
        $success = $error = false;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('draftdate', (string) $request->getPayload()->get('_token'))) {
                throw new AccessDeniedHttpException('Invalid CSRF token');
            }

            $attend = $request->request->all('attend');
            try {
                $success = $schedule->submitVotes($userId, $season, $attend);
            } catch (\InvalidArgumentException) {
                throw new AccessDeniedHttpException('Malformed vote submission');
            }
            $error = !$success;
        }

        return $this->render('draftdate/index.html.twig', [
            'loggedIn' => true,
            'season'   => $season,
            'dates'    => $schedule->memberDates($userId, $season),
            'success'  => $success,
            'error'    => $error,
            'maxNo'    => DraftScheduleService::MAX_NO_VOTES,
        ]);
    }
}
