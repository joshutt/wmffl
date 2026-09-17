<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/activate/ URLs to the Symfony
 * routes (pattern: LegacyTransactionRedirectController from Phase 4).
 *
 * currentscore.php and scoreFunctions.php are deliberately absent: the
 * box score stays on the LegacyBridge until Phase 17.
 *
 * No /activate/index.php alias - Symfony strips a trailing index.php as
 * the front controller, so such a route can never match.
 */
class LegacyActivationRedirectController extends AbstractController
{
    #[Route('/activate/activations', name: 'legacy_activations')]
    #[Route('/activate/activations.php', name: 'legacy_activations_php')]
    #[Route('/activate/currentactivations', name: 'legacy_current_activations')]
    #[Route('/activate/currentactivations.php', name: 'legacy_current_activations_php')]
    public function activations(Request $request): Response
    {
        $params = [];
        foreach (['season', 'week'] as $key) {
            if ($request->query->has($key)) {
                $params[$key] = $request->query->getInt($key);
            }
        }

        return $this->redirectToRoute('activations', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/activate/submitactivations', name: 'legacy_submit_activations', methods: ['GET', 'POST'])]
    #[Route('/activate/submitactivations.php', name: 'legacy_submit_activations_php', methods: ['GET', 'POST'])]
    public function submit(Request $request): Response
    {
        $params = [];
        if ($request->query->has('week')) {
            $params['week'] = $request->query->getInt('week');
        }

        return $this->redirectToRoute('activations_submit', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The old save handler and its orphaned thank-you page. GET|POST so
     * a stale legacy form redirects instead of 405ing; the redirect
     * drops the POST body, which is the point - those submissions are
     * not accepted unauthenticated and untokenized any more.
     */
    #[Route('/activate/processActivations', name: 'legacy_process_activations', methods: ['GET', 'POST'])]
    #[Route('/activate/processActivations.php', name: 'legacy_process_activations_php', methods: ['GET', 'POST'])]
    #[Route('/activate/submitthanks', name: 'legacy_submit_thanks')]
    #[Route('/activate/submitthanks.php', name: 'legacy_submit_thanks_php')]
    public function process(): Response
    {
        return $this->redirectToRoute('activations_submit', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /** Deleted, not migrated: a phpinfo() dump nothing linked to. */
    #[Route('/activate/info', name: 'legacy_activate_info')]
    #[Route('/activate/info.php', name: 'legacy_activate_info_php')]
    public function info(): Response
    {
        return $this->redirectToRoute('activations', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
