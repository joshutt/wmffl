<?php

namespace App\Controller;

use App\Service\DraftResultsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DraftResultsController extends AbstractController
{
    public function __construct(
        private readonly DraftResultsService $draftResultsService
    ) {
    }

    #[Route('/transactions/draftresults/{year?}', name: 'transactions_draft_results', requirements: ['year' => '\d+'])]
    public function index(Request $request, ?int $year = null): Response
    {
        $year ??= $this->draftResultsService->resolveDefaultYear();

        if (!$this->draftResultsService->isReachable($year)) {
            throw $this->createNotFoundException();
        }

        $filters = [
            'round' => $request->query->get('round'),
            'pick' => $request->query->get('pick'),
            'team' => $request->query->get('team'),
            'pos' => $request->query->get('pos'),
            'nfl' => $request->query->get('nfl'),
        ];

        return $this->render(
            'transactions/draftresults.html.twig',
            $this->draftResultsService->getBoard($year, $filters) + ['year' => $year]
        );
    }
}
