<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/transactions/ URLs to the
 * Symfony routes (pattern: LegacyTeamRedirectController from Phase 3).
 * The .php aliases cover archival history pages and old bookmarks.
 * trades/ is untouched — it still runs via the LegacyBridge until
 * Phase 6.
 */
class LegacyTransactionRedirectController extends AbstractController
{
    // No /transactions/index.php alias: Symfony strips a trailing
    // index.php as the front controller, so such a route never matches
    // (Phase 3 gotcha); the bare directory URL redirects natively.
    #[Route('/transactions/transactions', name: 'legacy_transactions')]
    #[Route('/transactions/transactions.php', name: 'legacy_transactions_php')]
    public function transactions(Request $request): Response
    {
        $params = [];
        foreach (['year', 'month'] as $key) {
            if ($value = $request->query->get($key)) {
                $params[$key] = (int) $value;
            }
        }

        return $this->redirectToRoute('transactions_history', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/transactions/displayWaiverOrder', name: 'legacy_waiver_order')]
    #[Route('/transactions/displayWaiverOrder.php', name: 'legacy_waiver_order_php')]
    #[Route('/transactions/listwaiverpicks', name: 'legacy_waiver_picks')]
    #[Route('/transactions/listwaiverpicks.php', name: 'legacy_waiver_picks_php')]
    public function waivers(): Response
    {
        return $this->redirectToRoute('transactions_waivers', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/transactions/showprotections', name: 'legacy_show_protections')]
    #[Route('/transactions/showprotections.php', name: 'legacy_show_protections_php')]
    public function showProtections(Request $request): Response
    {
        $params = [];
        if ($season = $request->query->get('season')) {
            $params['season'] = (int) $season;
        }
        if ($order = $request->query->get('order')) {
            $params['order'] = $order === 'pos' ? 'pos' : 'team';
        }

        return $this->redirectToRoute('transactions_protections_show', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/transactions/list.php', name: 'legacy_transactions_list_php', methods: ['GET', 'POST'])]
    public function list(): Response
    {
        return $this->redirectToRoute('transactions_list', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /** Stale confirm forms and bookmarks land back on the search page */
    #[Route('/transactions/confirm.php', name: 'legacy_transactions_confirm_php', methods: ['GET', 'POST'])]
    public function confirm(): Response
    {
        return $this->redirectToRoute('transactions_list', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/transactions/injuredReserve', name: 'legacy_ir')]
    #[Route('/transactions/injuredReserve.php', name: 'legacy_ir_php')]
    public function injuredReserve(): Response
    {
        return $this->redirectToRoute('transactions_ir', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /** The legacy AJAX endpoint; stale scripts get pointed at the page */
    #[Route('/transactions/updateIR.php', name: 'legacy_update_ir_php', methods: ['GET', 'POST'])]
    public function updateIr(): Response
    {
        return $this->redirectToRoute('transactions_ir', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/transactions/protections.php', name: 'legacy_protections_php')]
    public function protections(): Response
    {
        return $this->redirectToRoute('transactions_protections', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/transactions/saveprotections', name: 'legacy_save_protections', methods: ['GET', 'POST'])]
    #[Route('/transactions/saveprotections.php', name: 'legacy_save_protections_php', methods: ['GET', 'POST'])]
    public function saveProtections(): Response
    {
        return $this->redirectToRoute('transactions_protections', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Deleted, not migrated: the draft-order word game and the old
     * standalone injury report. Their URLs land on the transactions hub.
     */
    #[Route('/transactions/draftorder/{path}', name: 'legacy_draftorder', requirements: ['path' => '.*'], defaults: ['path' => ''])]
    #[Route('/transactions/injury/{path}', name: 'legacy_injury_dir', requirements: ['path' => '.*'], defaults: ['path' => ''])]
    public function deletedSubdirectories(): Response
    {
        return $this->redirectToRoute('transactions_history', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
