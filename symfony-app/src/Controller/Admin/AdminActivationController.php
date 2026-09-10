<?php

namespace App\Controller\Admin;

use App\Controller\ActivationController;
use App\Repository\ActivationRepository;
use App\Service\ActivationService;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Commissioner lineup override: set any team's lineup for any week,
 * after kickoff if need be.
 *
 * "Become Team" cannot do this — impersonating an owner still hits the
 * member form's kickoff locks, so a lineup that needed fixing after the
 * games started had to be repaired in the database by hand. Here the
 * locks and the post-week-14 bench are both bypassed. The position
 * rules still apply, because a commissioner should not create an
 * illegal lineup by accident; "save anyway" is there for the times the
 * record genuinely was illegal (the league fines those rather than
 * rejecting them).
 */
#[Route('/admin/activations')]
class AdminActivationController extends AbstractAdminController
{
    private const CSRF_ID = 'admin_activations';

    public function __construct(
        private readonly ActivationService $activationService,
        private readonly ActivationRepository $activations,
        private readonly SeasonWeekService $seasonWeek
    ) {
    }

    #[Route('', name: 'admin_activations', methods: ['GET'])]
    public function index(Request $request, AuthenticationService $auth): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season = $request->query->has('season')
            ? $request->query->getInt('season')
            : $this->seasonWeek->getCurrentSeason();

        return $this->render('admin/activations/index.html.twig', [
            'season' => $season,
            // Week 0 is the off-season placeholder, never a real week to
            // open a lineup for.
            'week' => max(1, $this->seasonWeek->getCurrentWeek()),
            'teams' => $this->activations->getTeamOptions($season),
            'weeks' => $this->activations->getAllWeeks($season),
        ]);
    }

    #[Route('/{teamId}/{season}/{week}', name: 'admin_activations_edit', requirements: ['teamId' => '\d+', 'season' => '\d+', 'week' => '\d+'], methods: ['GET'])]
    public function edit(int $teamId, int $season, int $week, AuthenticationService $auth): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        if ($week < 1) {
            return $this->redirectToRoute('admin_activations', ['season' => $season]);
        }

        return $this->renderForm($teamId, $season, $week, null, []);
    }

    #[Route('/{teamId}/{season}/{week}', name: 'admin_activations_save', requirements: ['teamId' => '\d+', 'season' => '\d+', 'week' => '\d+'], methods: ['POST'])]
    public function save(int $teamId, int $season, int $week, Request $request, AuthenticationService $auth): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);
        if ($week < 1) {
            // Never reachable through the picker, which no longer offers
            // week 0 - only a hand-crafted URL lands here.
            $this->addFlash('error', 'Activations cannot be set for the off-season.');

            return $this->redirectToRoute('admin_activations', ['season' => $season]);
        }

        $allowIllegal = $request->request->getBoolean('allowIllegal');
        $submitted = ActivationController::readSelections(
            $request,
            $this->activationService->getLineupRules($season)->positions()
        );

        $errors = $this->activationService->save(
            $season,
            $week,
            $teamId,
            $submitted['selections'],
            $submitted['actingHcId'],
            allowIllegal: $allowIllegal,
            bypassLocks: true
        );

        if ($errors === []) {
            $this->addFlash('success', sprintf(
                'Lineup saved for %d, week %d%s',
                $season, $week, $allowIllegal ? ' (illegal lineup kept as submitted)' : ''
            ));

            return $this->redirectToRoute('admin_activations_edit', [
                'teamId' => $teamId, 'season' => $season, 'week' => $week,
            ]);
        }

        return $this->renderForm($teamId, $season, $week, $submitted['view'], $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function renderForm(
        int $teamId,
        int $season,
        int $week,
        ?array $submitted,
        array $errors,
        int $status = Response::HTTP_OK
    ): Response {
        $view = $this->activationService->buildSubmitView($season, $week, $teamId, $submitted, bypassLocks: true);
        $teams = $this->activations->getTeamOptions($season);
        $names = array_column($teams, 'name', 'teamid');

        return $this->render('admin/activations/edit.html.twig', $view + [
            'loggedIn' => true,
            'errors' => $errors,
            'teamName' => $names[$teamId] ?? "Team $teamId",
            'weeks' => $this->activations->getAllWeeks($season),
            'teams' => $teams,
            'admin' => true,
        ], new Response(status: $status));
    }
}
