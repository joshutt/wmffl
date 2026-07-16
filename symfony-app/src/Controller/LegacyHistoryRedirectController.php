<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/history/ top-level URLs
 * (Phase 9a) to the Symfony routes, .php aliases included for
 * archival links. paststreaks.php was dropped without replacement and
 * gets no redirect. No /history/index.php alias: Symfony strips a
 * trailing index.php as the front controller, so such a route never
 * matches — the bare /history/ URL redirects natively.
 */
class LegacyHistoryRedirectController extends AbstractController
{
    #[Route('/history/pastchamps.php', name: 'legacy_history_pastchamps_php')]
    public function pastchamps(): Response
    {
        return $this->redirectToRoute('history_pastchamps', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/history/pastdrafts.php', name: 'legacy_history_pastdrafts_php')]
    public function pastdrafts(): Response
    {
        return $this->redirectToRoute('history_pastdrafts', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/history/alltimerecords.php', name: 'legacy_history_alltimerecords_php')]
    public function alltimerecords(): Response
    {
        return $this->redirectToRoute('history_alltimerecords', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/history/recordseason.php', name: 'legacy_history_recordseason_php')]
    public function recordseason(): Response
    {
        return $this->redirectToRoute('history_recordseason', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/history/recordsweek.php', name: 'legacy_history_recordsweek_php')]
    public function recordsweek(): Response
    {
        return $this->redirectToRoute('history_recordsweek', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/history/teammoney.php', name: 'legacy_history_teammoney_php')]
    public function teammoney(Request $request): Response
    {
        $params = $request->query->has('season')
            ? ['season' => $request->query->get('season')]
            : [];

        return $this->redirectToRoute('history_teammoney', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * 2024Season/teammoney.php was not a frozen snapshot like its
     * older siblings — it was a second copy of the dynamic ledger
     * hardwired to default to 2024, retired along with the original.
     * The real frozen snapshots (≤2023) stay for Phase 9b.
     */
    #[Route('/history/2024Season/teammoney', name: 'legacy_history_teammoney_2024')]
    #[Route('/history/2024Season/teammoney.php', name: 'legacy_history_teammoney_2024_php')]
    public function teammoney2024(): Response
    {
        return $this->redirectToRoute('history_teammoney', ['season' => 2024], Response::HTTP_MOVED_PERMANENTLY);
    }
}
