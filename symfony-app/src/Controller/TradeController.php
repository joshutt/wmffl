<?php

namespace App\Controller;

use App\Enum\OfferCommentActionEnum;
use App\Repository\TradeOfferRepository;
use App\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The trade screen, ported from football/transactions/trades/
 * tradescreen.php: pending offers involving your team with their terms,
 * comment history (new capability), per-offer action buttons, and the
 * "Offer New Trade" team picker.
 */
class TradeController extends AbstractController
{
    public function __construct(
        private readonly TradeOfferRepository $offers,
        private readonly AuthenticationService $auth
    ) {
    }

    #[Route('/trades', name: 'trades_screen')]
    public function index(): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('trades/index.html.twig', ['loggedIn' => false]);
        }

        $teamId = (int) $this->auth->getTeamNumber();

        $offers = [];
        foreach ($this->offers->findPendingOffersForTeam($teamId) as $offer) {
            // Read-time expiry: offers past the 7-day window are not
            // actionable and drop off the screen (legacy relied on the
            // nightly job alone)
            if ($offer['status'] !== 'Pending') {
                continue;
            }

            $otherTeamId = $offer['teamAId'] === $teamId ? $offer['teamBId'] : $offer['teamAId'];
            $isMyMove = $this->offers->isTeamsMove($offer, $teamId);

            $offers[] = [
                'offerId' => $offer['offerId'],
                'otherTeamName' => $offer['teamAId'] === $teamId ? $offer['teamBName'] : $offer['teamAName'],
                'date' => $offer['date'],
                'expires' => $offer['expires'],
                'isMyMove' => $isMyMove,
                'statusLine' => $isMyMove
                    ? 'They Made Offer, Pending Your Response'
                    : 'You Made Offer, Pending Their Response',
                'youReceive' => $offer['terms'][$otherTeamId],
                'theyReceive' => $offer['terms'][$teamId],
                'comments' => $this->labelledComments($offer['offerId']),
            ];
        }

        return $this->render('trades/index.html.twig', [
            'loggedIn' => true,
            'offers' => $offers,
            'teams' => array_filter(
                $this->offers->getActiveTeams(),
                static fn (array $team) => $team['teamid'] !== $teamId
            ),
        ]);
    }

    /** Comment history across the amendment chain with display labels. */
    private function labelledComments(int $offerId): array
    {
        return array_map(static function (array $comment) {
            $action = OfferCommentActionEnum::tryFrom($comment['action']);
            $comment['actionLabel'] = $action?->label() ?? $comment['action'];
            return $comment;
        }, $this->offers->getCommentHistory($offerId));
    }
}
