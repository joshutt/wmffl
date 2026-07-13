<?php

namespace App\Tests\Controller;

use App\Controller\TradeController;
use App\Repository\TradeOfferRepository;
use App\Service\AuthenticationService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
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

    private function makeController(
        TradeOfferRepository $repo,
        bool $loggedIn,
        ?int $teamNum = null
    ): TradeController {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        return new class($repo, $auth) extends TradeController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return $response ?? new Response();
            }
        };
    }
}
