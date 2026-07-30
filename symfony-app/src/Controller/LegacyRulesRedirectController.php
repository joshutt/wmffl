<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/rules/ proposal & ballot pages
 * to the Symfony routes. The static rulebook (rules{year}.php, RulesSup*)
 * is intentionally NOT handled here — it stays for Phase 14. These routes
 * must exist so the deleted pages don't fall through to LegacyBridge,
 * which 500s on a missing include.
 */
class LegacyRulesRedirectController extends AbstractController
{
    /**
     * proposals{year}[suffix].php -> /rules/proposals?season=YYYY. The
     * suffix catches the odd filenames (proposals2011a.php,
     * proposals20026detail.php); the season is the first 4-digit run.
     */
    #[Route('/rules/proposals{suffix}.php', name: 'legacy_proposals_year', requirements: ['suffix' => '\d[\w-]*'])]
    public function proposalsYear(string $suffix): Response
    {
        $params = preg_match('/(\d{4})/', $suffix, $m) ? ['season' => (int) $m[1]] : [];

        return $this->redirectToRoute('proposals_list', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    /** The bare /rules/proposals.php (no year) -> current-season list. */
    #[Route('/rules/proposals.php', name: 'legacy_proposals_php')]
    public function proposals(): Response
    {
        return $this->redirectToRoute('proposals_list', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * ballot.php / ballotcount.php / ballotthanks.php -> the new ballot.
     * (The extensionless /rules/ballot is the live BallotController route.)
     */
    #[Route('/rules/ballot.php', name: 'legacy_ballot_php', methods: ['GET', 'POST'])]
    #[Route('/rules/ballotcount.php', name: 'legacy_ballotcount_php', methods: ['GET', 'POST'])]
    #[Route('/rules/ballotthanks.php', name: 'legacy_ballotthanks_php')]
    #[Route('/rules/ballotthanks', name: 'legacy_ballotthanks')]
    public function ballot(): Response
    {
        return $this->redirectToRoute('ballot', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /** propose.php / proposesubmit.php -> the new submit form. */
    #[Route('/rules/propose.php', name: 'legacy_propose_php', methods: ['GET', 'POST'])]
    #[Route('/rules/propose', name: 'legacy_propose')]
    #[Route('/rules/proposesubmit.php', name: 'legacy_proposesubmit_php', methods: ['GET', 'POST'])]
    #[Route('/rules/proposesubmit', name: 'legacy_proposesubmit')]
    public function propose(): Response
    {
        return $this->redirectToRoute('proposals_submit_form', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
