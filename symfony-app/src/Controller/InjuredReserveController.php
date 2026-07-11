<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\InjuredReserveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Injured Reserve page and its AJAX add/remove endpoint, ported from
 * football/transactions/{injuredReserve,updateIR}.php. The JSON response
 * keeps the legacy plain-string shape that base/js/injury.js expects.
 */
class InjuredReserveController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'ir_update';

    public function __construct(
        private readonly InjuredReserveService $injuredReserve,
        private readonly AuthenticationService $auth
    ) {
    }

    #[Route('/transactions/ir', name: 'transactions_ir')]
    public function index(): Response
    {
        $eligible = $current = null;
        if ($this->auth->isLoggedIn()) {
            $teamId = (int) $this->auth->getTeamNumber();
            $eligible = $this->injuredReserve->getEligiblePlayers($teamId);
            $current = $this->injuredReserve->getCurrentIrPlayers($teamId);
        }

        return $this->render('transactions/ir.html.twig', [
            'eligible' => $eligible,
            'current' => $current,
        ]);
    }

    #[Route('/transactions/ir/update', name: 'transactions_ir_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return new Response('User is not logged in', Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_ID, (string) $request->getPayload()->get('_token'))) {
            return new Response('Invalid CSRF token', Response::HTTP_FORBIDDEN);
        }

        $method = $request->getPayload()->get('method');
        $playerId = (int) $request->getPayload()->get('playerid');
        $teamId = (int) $this->auth->getTeamNumber();

        if ($method === 'Add') {
            $message = $this->injuredReserve->addPlayerToIr($teamId, $playerId)
                ? "Player $playerId Added to IR"
                : "Unable to add $playerId to IR";
        } elseif ($method === 'Remove') {
            $message = $this->injuredReserve->removePlayerFromIr($teamId, $playerId)
                ? "Player $playerId removed from IR"
                : "Unable to remove $playerId from IR";
        } else {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($message);
    }
}
