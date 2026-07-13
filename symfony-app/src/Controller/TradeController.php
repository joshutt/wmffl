<?php

namespace App\Controller;

use App\Enum\OfferCommentActionEnum;
use App\Repository\TradeOfferRepository;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use App\Service\TradeValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The trade screen and offer builder, ported from
 * football/transactions/trades/ {tradescreen,edittrade}.php: pending
 * offers involving your team with their terms, comment history (new
 * capability), per-offer action buttons, the "Offer New Trade" team
 * picker, and the offer builder shared by new offers, amendments, and
 * counteroffers.
 */
class TradeController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'trade_offer';

    public function __construct(
        private readonly TradeOfferRepository $offers,
        private readonly AuthenticationService $auth,
        private readonly SeasonWeekService $seasonWeek,
        private readonly TradeValidationService $validation
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

    /**
     * The offer builder, shared by new offers (?to=<teamid>), amendments,
     * and counteroffers (?offerid=<id> — the amend/counter distinction is
     * derived at submit time from whose move it was). POST re-renders the
     * builder from submitted selections (the preview's Edit button).
     */
    #[Route('/trades/offer', name: 'trades_offer', methods: ['GET', 'POST'])]
    public function offerBuilder(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->redirectToRoute('trades_screen');
        }

        $context = $this->resolveBuilderContext($request);
        if ($context === null) {
            return $this->redirectToRoute('trades_screen');
        }

        $selections = $request->isMethod('POST')
            ? $this->parseSelections($request)
            : ($context['offer'] !== null
                ? $this->selectionsFromOffer($context['offer'], $context['teamId'], $context['otherTeamId'])
                : ['you' => self::EMPTY_SIDE, 'they' => self::EMPTY_SIDE]);

        return $this->renderBuilder($context, $selections, []);
    }

    private const EMPTY_SIDE = ['players' => [], 'picks' => [], 'points' => []];

    /**
     * Who is trading with whom, from ?offerid= (amend/counter, guarded:
     * my team must be in the offer and it must still be Pending) or
     * ?to= (new offer). Null means "bounce back to the trade screen".
     *
     * @return ?array{teamId: int, otherTeamId: int, otherTeamName: string, offer: ?array}
     */
    private function resolveBuilderContext(Request $request): ?array
    {
        $teamId = (int) $this->auth->getTeamNumber();
        $offerId = (int) ($request->getPayload()->get('offerid') ?: $request->query->get('offerid', 0));

        if ($offerId > 0) {
            $offer = $this->offers->findOffer($offerId);
            if ($offer === null
                || $offer['status'] !== 'Pending'
                || !in_array($teamId, [$offer['teamAId'], $offer['teamBId']], true)
            ) {
                return null;
            }
            $otherTeamId = $offer['teamAId'] === $teamId ? $offer['teamBId'] : $offer['teamAId'];

            return [
                'teamId' => $teamId,
                'otherTeamId' => $otherTeamId,
                'otherTeamName' => $offer['teamAId'] === $teamId ? $offer['teamBName'] : $offer['teamAName'],
                'offer' => $offer,
            ];
        }

        $otherTeamId = (int) ($request->getPayload()->get('to') ?: $request->query->get('to', 0));
        $otherTeamName = $otherTeamId > 0 && $otherTeamId !== $teamId
            ? $this->offers->getTeamName($otherTeamId)
            : null;
        if ($otherTeamName === null) {
            return null;
        }

        return [
            'teamId' => $teamId,
            'otherTeamId' => $otherTeamId,
            'otherTeamName' => $otherTeamName,
            'offer' => null,
        ];
    }

    /**
     * Selection state from a POSTed builder/preview form:
     * you_players[]/they_players[] (playerids), you_picks[]/they_picks[]
     * (draftpicks row ids), you_points[season]/they_points[season].
     */
    private function parseSelections(Request $request): array
    {
        $payload = $request->getPayload();
        $side = static fn (string $prefix) => [
            'players' => array_map('intval', $payload->all($prefix . '_players')),
            'picks' => array_map('intval', $payload->all($prefix . '_picks')),
            'points' => array_filter(array_map('intval', $payload->all($prefix . '_points'))),
        ];

        return ['you' => $side('you'), 'they' => $side('they')];
    }

    /**
     * Pre-select an existing offer's terms (amend/counter). Stored picks
     * are matched back to the giving team's currently-owned draftpicks
     * rows; a pick the team no longer owns simply drops out.
     */
    private function selectionsFromOffer(array $offer, int $teamId, int $otherTeamId): array
    {
        $minSeason = $this->validation->minPickSeason();

        $side = function (int $givingTeamId) use ($offer, $minSeason) {
            $terms = $offer['terms'][$givingTeamId];

            $pickIds = [];
            $owned = $this->offers->getOwnedFuturePicks($givingTeamId, $minSeason);
            foreach ($terms['picks'] as $pick) {
                foreach ($owned as $candidate) {
                    if ($candidate['season'] === $pick['season']
                        && $candidate['round'] === $pick['round']
                        && $candidate['orgTeamId'] === $pick['orgTeamId']
                        && !in_array($candidate['id'], $pickIds, true)
                    ) {
                        $pickIds[] = $candidate['id'];
                        break;
                    }
                }
            }

            $points = [];
            foreach ($terms['points'] as $point) {
                $points[$point['season']] = $point['points'];
            }

            return [
                'players' => array_column($terms['players'], 'playerid'),
                'picks' => $pickIds,
                'points' => $points,
            ];
        };

        return ['you' => $side($teamId), 'they' => $side($otherTeamId)];
    }

    /**
     * @param array{teamId: int, otherTeamId: int, otherTeamName: string, offer: ?array} $context
     * @param string[] $errors validation errors shown inline
     */
    private function renderBuilder(array $context, array $selections, array $errors): Response
    {
        $fromSeason = $this->seasonWeek->getCurrentSeason();
        $toSeason = $fromSeason + TradeValidationService::POINTS_FUTURE_SEASONS;
        $minPickSeason = $this->validation->minPickSeason();

        return $this->render('trades/offer.html.twig', [
            'offerId' => $context['offer']['offerId'] ?? 0,
            'otherTeamId' => $context['otherTeamId'],
            'otherTeamName' => $context['otherTeamName'],
            'errors' => $errors,
            'selections' => $selections,
            'you' => [
                'roster' => $this->offers->getTradeableRoster($context['teamId']),
                'picks' => $this->offers->getOwnedFuturePicks($context['teamId'], $minPickSeason),
                'points' => $this->offers->getPointsBalances($context['teamId'], $fromSeason, $toSeason),
            ],
            'they' => [
                'roster' => $this->offers->getTradeableRoster($context['otherTeamId']),
                'picks' => $this->offers->getOwnedFuturePicks($context['otherTeamId'], $minPickSeason),
                'points' => $this->offers->getPointsBalances($context['otherTeamId'], $fromSeason, $toSeason),
            ],
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
