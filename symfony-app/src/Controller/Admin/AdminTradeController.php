<?php

namespace App\Controller\Admin;

use App\Enum\OfferCommentActionEnum;
use App\Enum\OfferStatusEnum;
use App\Repository\TradeOfferRepository;
use App\Service\AuthenticationService;
use App\Service\TradeMailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Commissioner oversight of trade offers (new capability — legacy had no
 * admin tooling for trades): every offer with its terms and comment
 * history, filterable by status, and the power to void a pending offer
 * with a stored reason and notification to both teams.
 */
#[Route('/admin/trades')]
class AdminTradeController extends AbstractAdminController
{
    public const CSRF_TOKEN_ID = 'admin_trade_void';

    public function __construct(
        private readonly TradeOfferRepository $offers,
        private readonly AuthenticationService $auth,
        private readonly TradeMailer $mailer
    ) {
    }

    #[Route('', name: 'admin_trades')]
    public function index(Request $request): Response
    {
        if ($redirect = $this->requireCommissioner($this->auth)) {
            return $redirect;
        }

        $status = (string) $request->query->get('status', '');
        $statuses = array_map(static fn (OfferStatusEnum $case) => $case->value, OfferStatusEnum::cases());
        if (!in_array($status, $statuses, true)) {
            $status = '';
        }

        $offers = [];
        foreach ($this->offers->findOffers($status === '' ? null : $status) as $offer) {
            $offer['comments'] = array_map(static function (array $comment) {
                $action = OfferCommentActionEnum::tryFrom($comment['action']);
                $comment['actionLabel'] = $action?->label() ?? $comment['action'];
                return $comment;
            }, $this->offers->getCommentHistory($offer['offerId']));
            $offers[] = $offer;
        }

        return $this->render('admin/trades/index.html.twig', [
            'offers' => $offers,
            'statuses' => $statuses,
            'statusFilter' => $status,
        ]);
    }

    #[Route('/void/{id}', name: 'admin_trades_void', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function void(int $id, Request $request): Response
    {
        if ($redirect = $this->requireCommissioner($this->auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_TOKEN_ID);

        $offer = $this->offers->findOffer($id);
        if ($offer === null || $offer['status'] !== 'Pending') {
            $this->addFlash('error', 'Only a pending offer can be voided.');

            return $this->redirectToRoute('admin_trades');
        }

        $reason = (string) $request->getPayload()->get('reason', '');
        $this->offers->setStatus($id, 'Reject');
        $this->offers->addComment(
            $id,
            (int) $this->auth->getTeamNumber(),
            OfferCommentActionEnum::Voided->value,
            $reason
        );
        $this->mailer->sendVoidedEmail($offer, $reason);

        $this->addFlash('success', "Offer $id voided.");

        return $this->redirectToRoute('admin_trades');
    }
}
