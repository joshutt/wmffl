<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminTradeController;
use App\Repository\TradeOfferRepository;
use App\Service\AuthenticationService;
use App\Service\TradeMailer;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminTradeControllerTest extends TestCase
{
    public function testNonCommissionerIsRedirectedFromTheIndex(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->expects($this->never())->method('findOffers');

        $controller = $this->makeController($repo, commissioner: false);
        $response = $controller->index(new Request());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testNonCommissionerCannotVoid(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->expects($this->never())->method('setStatus');

        $controller = $this->makeController($repo, commissioner: false);
        $response = $controller->void(100, Request::create('/admin/trades/void/100', 'POST'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexListsOffersWithLabelledCommentHistories(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->expects($this->once())->method('findOffers')->with(null)->willReturn([$this->offer()]);
        $repo->method('getCommentHistory')->willReturn([
            ['teamId' => 2, 'teamName' => 'Mustangs', 'action' => 'offered',
                'date' => new \DateTimeImmutable('2026-07-10'), 'comment' => 'Deal?'],
        ]);

        $controller = $this->makeController($repo, commissioner: true);
        $controller->index(new Request());

        $this->assertSame('admin/trades/index.html.twig', $controller->renderedView);
        $offers = $controller->renderedParams['offers'];
        $this->assertCount(1, $offers);
        $this->assertSame('Offered', $offers[0]['comments'][0]['actionLabel']);
    }

    public function testIndexPassesAValidStatusFilterThrough(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->expects($this->once())->method('findOffers')->with('Accept')->willReturn([]);

        $controller = $this->makeController($repo, commissioner: true);
        $controller->index(new Request(query: ['status' => 'Accept']));

        $this->assertSame('Accept', $controller->renderedParams['statusFilter']);
    }

    public function testIndexIgnoresAnUnknownStatusFilter(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->expects($this->once())->method('findOffers')->with(null)->willReturn([]);

        $controller = $this->makeController($repo, commissioner: true);
        $controller->index(new Request(query: ['status' => 'DROP TABLE']));

        $this->assertSame('', $controller->renderedParams['statusFilter']);
    }

    public function testVoidInvalidCsrfIsDenied(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);

        $controller = $this->makeController($repo, commissioner: true, csrfValid: false);

        $this->expectException(AccessDeniedHttpException::class);
        $controller->void(100, Request::create('/admin/trades/void/100', 'POST', ['_token' => 'bad']));
    }

    public function testVoidRejectsStoresReasonAndNotifiesBothTeams(): void
    {
        $offer = $this->offer();
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->method('findOffer')->willReturn($offer);
        $repo->expects($this->once())->method('setStatus')->with(100, 'Reject');
        $repo->expects($this->once())->method('addComment')->with(100, 9, 'voided', 'Collusion suspected');

        $mailer = $this->createMock(TradeMailer::class);
        $mailer->expects($this->once())->method('sendVoidedEmail')->with($offer, 'Collusion suspected');

        $controller = $this->makeController($repo, commissioner: true, mailer: $mailer);
        $controller->void(100, Request::create('/admin/trades/void/100', 'POST', [
            '_token' => 'ok', 'reason' => 'Collusion suspected',
        ]));

        $this->assertSame(['admin_trades', []], $controller->redirectedTo);
        $this->assertSame(['success', 'Offer 100 voided.'], $controller->flashed);
    }

    public function testVoidRefusesASettledOffer(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->method('findOffer')->willReturn($this->offer(status: 'Accept'));
        $repo->expects($this->never())->method('setStatus');

        $mailer = $this->createMock(TradeMailer::class);
        $mailer->expects($this->never())->method('sendVoidedEmail');

        $controller = $this->makeController($repo, commissioner: true, mailer: $mailer);
        $controller->void(100, Request::create('/admin/trades/void/100', 'POST', ['_token' => 'ok']));

        $this->assertSame(['admin_trades', []], $controller->redirectedTo);
        $this->assertSame('error', $controller->flashed[0]);
    }

    public function testVoidRefusesAnExpiredOffer(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->method('findOffer')->willReturn($this->offer(status: 'Expired'));
        $repo->expects($this->never())->method('setStatus');

        $controller = $this->makeController($repo, commissioner: true);
        $controller->void(100, Request::create('/admin/trades/void/100', 'POST', ['_token' => 'ok']));

        $this->assertSame('error', $controller->flashed[0]);
    }

    // ---- helpers ----

    private function offer(string $status = 'Pending'): array
    {
        return [
            'offerId' => 100,
            'teamAId' => 2,
            'teamAName' => 'Mustangs',
            'teamBId' => 5,
            'teamBName' => 'Rhinos',
            'status' => $status,
            'date' => new \DateTimeImmutable('2026-07-10 09:00:00'),
            'expires' => new \DateTimeImmutable('2026-07-17 09:00:00'),
            'lastOfferTeamId' => 2,
            'prevOfferId' => null,
            'terms' => [
                2 => ['players' => [], 'picks' => [], 'points' => []],
                5 => ['players' => [], 'picks' => [], 'points' => []],
            ],
        ];
    }

    private function makeController(
        TradeOfferRepository $repo,
        bool $commissioner,
        ?TradeMailer $mailer = null,
        bool $csrfValid = true
    ): AdminTradeController {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);
        $auth->method('getTeamNumber')->willReturn(9);

        $mailer ??= $this->createStub(TradeMailer::class);

        return new class($repo, $auth, $mailer, $csrfValid) extends AdminTradeController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;
            public ?array $flashed = null;

            public function __construct($repo, $auth, $mailer, private readonly bool $csrfValid)
            {
                parent::__construct($repo, $auth, $mailer);
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return $response ?? new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashed = [$type, $message];
            }

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }
        };
    }
}
