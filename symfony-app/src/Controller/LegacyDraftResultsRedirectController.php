<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/history/{year}Season/
 * draftresults pages (Phase 16b) to the new
 * /transactions/draftresults/{year} route. One route covers both legacy
 * filename forms (extensionless and .php) across every year that ever had
 * a draftresults wrapper, 2007-2025 (Phase 16a's
 * LegacyScheduleRedirectController is the template). methods: GET/POST so
 * the legacy POST-to-self filter form redirects rather than 405ing.
 */
class LegacyDraftResultsRedirectController extends AbstractController
{
    #[Route(
        '/history/{year}Season/{file}',
        name: 'legacy_draft_results_year_redirect',
        requirements: [
            'year' => '20(0[7-9]|1[0-9]|2[0-5])',
            'file' => 'draftresults|draftresults\.php',
        ],
        methods: ['GET', 'POST']
    )]
    public function year(int $year): Response
    {
        return $this->redirectToRoute('transactions_draft_results', ['year' => $year], Response::HTTP_MOVED_PERMANENTLY);
    }
}
