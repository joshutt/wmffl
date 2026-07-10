<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\ProtectionsService;
use App\Service\SeasonWeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Protection selection form and save, ported from football/transactions/
 * {protections,saveprotections}.php. Unlike legacy, the save endpoint
 * re-checks login, deadline and paid standing rather than trusting the
 * form to have hidden itself.
 */
class ProtectionsController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'protections_save';

    public function __construct(
        private readonly ProtectionsService $protections,
        private readonly SeasonWeekService $seasonWeek,
        private readonly AuthenticationService $auth
    ) {
    }

    #[Route('/transactions/protections', name: 'transactions_protections')]
    public function form(): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('transactions/protections.html.twig', ['points' => null, 'loggedIn' => false]);
        }

        $teamId = (int) $this->auth->getTeamNumber();
        $season = $this->seasonWeek->getCurrentSeason();

        return $this->render('transactions/protections.html.twig', [
            'loggedIn' => true,
            'deadline' => $this->protections->getDeadline(),
            'deadlinePassed' => $this->protections->isDeadlinePassed(),
            'points' => $this->protections->getPointsSummary($teamId, $season),
            'roster' => $this->protections->getRosterWithCosts($teamId, $season),
        ]);
    }

    #[Route('/transactions/protections/save', name: 'transactions_protections_save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('transactions/protections_saved.html.twig', ['error' => 'not_logged_in'], new Response(status: Response::HTTP_UNAUTHORIZED));
        }
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_ID, (string) $request->getPayload()->get('_token'))) {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }

        $teamId = (int) $this->auth->getTeamNumber();
        $season = $this->seasonWeek->getCurrentSeason();

        if ($this->protections->isDeadlinePassed()) {
            return $this->render('transactions/protections_saved.html.twig', ['error' => 'deadline']);
        }
        $points = $this->protections->getPointsSummary($teamId, $season);
        if (!$points || !$points['paid']) {
            return $this->render('transactions/protections_saved.html.twig', ['error' => 'unpaid']);
        }

        $result = $this->protections->saveProtections(
            $teamId,
            $season,
            (array) $request->getPayload()->all('protect')
        );

        if (!$result['ok']) {
            return $this->render('transactions/protections_saved.html.twig', [
                'error' => 'over_budget',
                'spent' => $result['totalCost'],
                'allowed' => $result['allowed'],
            ]);
        }

        return $this->render('transactions/protections_saved.html.twig', [
            'error' => null,
            'saved' => $this->protections->getSavedProtections($teamId, $season),
            'totalCost' => $result['totalCost'],
        ]);
    }
}
