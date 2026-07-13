<?php

namespace App\Tests\Controller;

use App\Controller\TradeController;
use App\Repository\TradeOfferRepository;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use App\Service\TradeValidationService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class TradeControllerTest extends TestCase
{
    public function testAnonymousGetsTheLoginPromptAndNoData(): void
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->expects($this->never())->method('findPendingOffersForTeam');

        $controller = $this->makeController($repo, loggedIn: false);
        $controller->index();

        $this->assertSame('trades/index.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['loggedIn']);
        $this->assertArrayNotHasKey('offers', $controller->renderedParams);
    }

    public function testOfferTheyMadeShowsAcceptSideStatusAndTermsBothWays(): void
    {
        // Team 5 made the last offer; we are team 2, so it's our move
        $repo = $this->repoWithOffers([$this->offer(lastOfferTeamId: 5)]);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->index();

        $offers = $controller->renderedParams['offers'];
        $this->assertCount(1, $offers);
        $this->assertTrue($offers[0]['isMyMove']);
        $this->assertSame('They Made Offer, Pending Your Response', $offers[0]['statusLine']);
        $this->assertSame('Rhinos', $offers[0]['otherTeamName']);
        // You receive what the OTHER team (5) gives up
        $this->assertSame('Bo Jackson', $offers[0]['youReceive']['players'][0]['name']);
        $this->assertSame('Al Kaline', $offers[0]['theyReceive']['players'][0]['name']);
        $this->assertEquals(new \DateTimeImmutable('2026-07-10 09:00:00'), $offers[0]['date']);
        $this->assertEquals(new \DateTimeImmutable('2026-07-17 09:00:00'), $offers[0]['expires']);
    }

    public function testOfferIMadeShowsWithdrawSideStatus(): void
    {
        $repo = $this->repoWithOffers([$this->offer(lastOfferTeamId: 2)]);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->index();

        $offers = $controller->renderedParams['offers'];
        $this->assertFalse($offers[0]['isMyMove']);
        $this->assertSame('You Made Offer, Pending Their Response', $offers[0]['statusLine']);
    }

    public function testExpiredOfferIsNotRendered(): void
    {
        $repo = $this->repoWithOffers([
            $this->offer(status: 'Expired'),
            $this->offer(offerId: 101),
        ]);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->index();

        $offers = $controller->renderedParams['offers'];
        $this->assertCount(1, $offers);
        $this->assertSame(101, $offers[0]['offerId']);
    }

    public function testCommentsCarryDisplayLabels(): void
    {
        $repo = $this->repoWithOffers([$this->offer()], comments: [
            ['teamId' => 5, 'teamName' => 'Rhinos', 'action' => 'countered',
                'date' => new \DateTimeImmutable('2026-07-11'), 'comment' => 'My counter'],
            ['teamId' => 9, 'teamName' => 'League', 'action' => 'voided',
                'date' => new \DateTimeImmutable('2026-07-12'), 'comment' => 'Nope'],
        ]);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->index();

        $comments = $controller->renderedParams['offers'][0]['comments'];
        $this->assertSame('Countered', $comments[0]['actionLabel']);
        $this->assertSame('Voided by league', $comments[1]['actionLabel']);
    }

    public function testNewTradeDropdownListsActiveTeamsWithoutMyOwn(): void
    {
        $repo = $this->repoWithOffers([]);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->index();

        $this->assertSame(
            [5, 7],
            array_column($controller->renderedParams['teams'], 'teamid')
        );
    }

    // ---- GET/POST /trades/offer (builder) ----

    public function testBuilderAnonymousIsBouncedToTheTradeScreen(): void
    {
        $controller = $this->makeController($this->repoWithOffers([]), loggedIn: false);
        $controller->offerBuilder(Request::create('/trades/offer?to=5'));

        $this->assertSame(['trades_screen', []], $controller->redirectedTo);
    }

    public function testNewOfferBuilderRendersBothSidesEmptySelections(): void
    {
        $repo = $this->builderRepo();

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->offerBuilder(Request::create('/trades/offer?to=5'));

        $this->assertSame('trades/offer.html.twig', $controller->renderedView);
        $params = $controller->renderedParams;
        $this->assertSame(0, $params['offerId']);
        $this->assertSame('Rhinos', $params['otherTeamName']);
        $this->assertSame([], $params['errors']);
        $this->assertSame(['players' => [], 'picks' => [], 'points' => []], $params['selections']['you']);
        $this->assertSame('Al Kaline', $params['you']['roster'][0]['name']);
        $this->assertSame('Bo Jackson', $params['they']['roster'][0]['name']);
        $this->assertSame(9, $params['you']['picks'][0]['id']);
        $this->assertSame(12, $params['you']['points'][2026]);
    }

    public function testOfferingATradeToYourselfIsRefused(): void
    {
        $controller = $this->makeController($this->builderRepo(), loggedIn: true, teamNum: 2);
        $controller->offerBuilder(Request::create('/trades/offer?to=2'));

        $this->assertSame(['trades_screen', []], $controller->redirectedTo);
    }

    public function testAmendPreselectsTheExistingTerms(): void
    {
        $offer = $this->offer(lastOfferTeamId: 2);
        $offer['terms'][2]['picks'] = [
            ['season' => 2027, 'round' => 1, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs'],
        ];
        $offer['terms'][5]['points'] = [['season' => 2026, 'points' => 4]];
        $repo = $this->builderRepo(findOffer: $offer);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->offerBuilder(Request::create('/trades/offer?offerid=100'));

        $selections = $controller->renderedParams['selections'];
        $this->assertSame([50], $selections['you']['players']);
        $this->assertSame([9], $selections['you']['picks'], 'stored pick matched to owned draftpicks row');
        $this->assertSame([60], $selections['they']['players']);
        $this->assertSame([2026 => 4], $selections['they']['points']);
    }

    public function testBuilderRefusesAnOfferMyTeamIsNotPartTo(): void
    {
        $offer = $this->offer();
        $repo = $this->builderRepo(findOffer: $offer);

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 9);
        $controller->offerBuilder(Request::create('/trades/offer?offerid=100'));

        $this->assertSame(['trades_screen', []], $controller->redirectedTo);
    }

    public function testBuilderRefusesASettledOffer(): void
    {
        $repo = $this->builderRepo(findOffer: $this->offer(status: 'Accept'));

        $controller = $this->makeController($repo, loggedIn: true, teamNum: 2);
        $controller->offerBuilder(Request::create('/trades/offer?offerid=100'));

        $this->assertSame(['trades_screen', []], $controller->redirectedTo);
    }

    public function testPostReRendersWithTheSubmittedSelections(): void
    {
        $controller = $this->makeController($this->builderRepo(), loggedIn: true, teamNum: 2);
        $controller->offerBuilder(Request::create('/trades/offer', 'POST', [
            'to' => '5',
            'you_players' => ['50'],
            'they_players' => ['60'],
            'you_points' => ['2026' => '5', '2027' => '0'],
        ]));

        $selections = $controller->renderedParams['selections'];
        $this->assertSame([50], $selections['you']['players']);
        $this->assertSame([60], $selections['they']['players']);
        $this->assertSame([2026 => 5], $selections['you']['points'], 'zero amounts dropped');
    }

    // ---- helpers ----

    private function offer(
        int $offerId = 100,
        string $status = 'Pending',
        int $lastOfferTeamId = 5
    ): array {
        $date = new \DateTimeImmutable('2026-07-10 09:00:00');

        return [
            'offerId' => $offerId,
            'teamAId' => 2,
            'teamAName' => 'Mustangs',
            'teamBId' => 5,
            'teamBName' => 'Rhinos',
            'status' => $status,
            'date' => $date,
            'expires' => $date->modify('+7 days'),
            'lastOfferTeamId' => $lastOfferTeamId,
            'prevOfferId' => null,
            'terms' => [
                2 => ['players' => [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']], 'picks' => [], 'points' => []],
                5 => ['players' => [['playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'nflteam' => 'LV']], 'picks' => [], 'points' => []],
            ],
        ];
    }

    private function repoWithOffers(array $offers, array $comments = []): TradeOfferRepository
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->method('findPendingOffersForTeam')->willReturn($offers);
        $repo->method('isTeamsMove')->willReturnCallback(
            static fn (array $offer, int $teamId) => $offer['lastOfferTeamId'] !== $teamId
        );
        $repo->method('getCommentHistory')->willReturn($comments);
        $repo->method('getActiveTeams')->willReturn([
            ['teamid' => 2, 'name' => 'Mustangs'],
            ['teamid' => 5, 'name' => 'Rhinos'],
            ['teamid' => 7, 'name' => 'Third Team'],
        ]);

        return $repo;
    }

    /** Repo stub with builder data for teams 2 (you) and 5 (they). */
    private function builderRepo(?array $findOffer = null): TradeOfferRepository
    {
        $repo = $this->createMock(TradeOfferRepository::class);
        $repo->method('findOffer')->willReturn($findOffer);
        $repo->method('getTeamName')->willReturnCallback(
            static fn (int $teamId) => [2 => 'Mustangs', 5 => 'Rhinos'][$teamId] ?? null
        );
        $repo->method('getTradeableRoster')->willReturnCallback(static fn (int $teamId) => match ($teamId) {
            2 => [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']],
            5 => [['playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'nflteam' => 'LV']],
            default => [],
        });
        $repo->method('getOwnedFuturePicks')->willReturnCallback(static fn (int $teamId) => match ($teamId) {
            2 => [['id' => 9, 'season' => 2027, 'round' => 1, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs']],
            default => [],
        });
        $repo->method('getPointsBalances')->willReturn([2026 => 12, 2027 => 30]);

        return $repo;
    }

    private function makeController(
        TradeOfferRepository $repo,
        bool $loggedIn,
        ?int $teamNum = null
    ): TradeController {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2026);
        $seasonWeek->method('getCurrentWeek')->willReturn(5);

        $validation = new TradeValidationService($repo, $seasonWeek);

        return new class($repo, $auth, $seasonWeek, $validation) extends TradeController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;

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
        };
    }
}
