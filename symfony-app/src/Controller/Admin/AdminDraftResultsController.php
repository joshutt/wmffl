<?php

namespace App\Controller\Admin;

use App\Repository\DraftPickRepository;
use App\Repository\PlayerRepository;
use App\Service\AuthenticationService;
use App\Service\DraftResultsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Minimal commissioner-only fix-up page for draft picks: set or clear a
 * pick's player. No pick reordering, ownership editing, or row add/delete
 * — Phase 16b decision 4. Reuses DraftResultsService::getBoard() for the
 * season's row list (same data the public page shows) and
 * PlayerRepository::searchPlayers() for the player picker, matching
 * AdminPlayerController's search shape.
 */
#[Route('/admin/draftresults')]
class AdminDraftResultsController extends AbstractAdminController
{
    /** Same cap as AdminPlayerController — a name search, not a browse. */
    public const MAX_RESULTS = 200;

    #[Route('/{year}', name: 'admin_draft_results', requirements: ['year' => '\d+'], defaults: ['year' => null])]
    public function index(
        Request $request,
        AuthenticationService $auth,
        DraftResultsService $draftResults,
        PlayerRepository $players,
        ?int $year = null
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $year ??= $draftResults->resolveDefaultYear();
        if (!$draftResults->isReachable($year)) {
            throw $this->createNotFoundException();
        }

        $editPickId = $request->query->getInt('editPick') ?: null;
        $q = trim((string) $request->query->get('q', ''));

        return $this->render('admin/draftresults/index.html.twig', $draftResults->getBoard($year, []) + [
            'year' => $year,
            'editPickId' => $editPickId,
            'q' => $q,
            'searchResults' => ($editPickId !== null && $q !== '')
                ? $players->searchPlayers(['q' => $q], 0, self::MAX_RESULTS)
                : null,
        ]);
    }

    #[Route('/{year}/pick/{id}', name: 'admin_draft_results_set', requirements: ['year' => '\d+', 'id' => '\d+'], methods: ['POST'])]
    public function setPick(
        int $year,
        int $id,
        Request $request,
        AuthenticationService $auth,
        DraftResultsService $draftResults,
        DraftPickRepository $draftPicks,
        PlayerRepository $players,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_draft_results');

        $pick = $draftPicks->find($id);
        // The cut-off is enforced on the pick's own season, not the {year}
        // path segment, so a hand-crafted POST can't smuggle a future-season
        // pick id in under a reachable year.
        if (!$pick || !$draftResults->isReachable($pick->getSeason())) {
            throw $this->createNotFoundException();
        }

        $playerId = $request->request->get('playerId', '');
        if ($playerId === '' || $playerId === null) {
            $pick->setPlayer(null);
            $em->flush();
            $this->addFlash('success', sprintf('Cleared Round %d, Pick %d', $pick->getRound(), $pick->getPick()));

            return $this->redirectToRoute('admin_draft_results', ['year' => $year]);
        }

        $player = $players->find((int) $playerId);
        if (!$player) {
            $this->addFlash('error', 'No such player');

            return $this->redirectToRoute('admin_draft_results', ['year' => $year]);
        }

        if ($draftPicks->isPlayerAlreadyDrafted($pick->getSeason(), $player->getId(), $pick->getId())) {
            $this->addFlash('error', sprintf(
                '%s %s was already drafted this season',
                $player->getFirstname(),
                $player->getLastname()
            ));

            return $this->redirectToRoute('admin_draft_results', ['year' => $year]);
        }

        $pick->setPlayer($player);
        $em->flush();
        $this->addFlash('success', sprintf(
            'Round %d, Pick %d set to %s %s',
            $pick->getRound(),
            $pick->getPick(),
            $player->getFirstname(),
            $player->getLastname()
        ));

        return $this->redirectToRoute('admin_draft_results', ['year' => $year]);
    }
}
