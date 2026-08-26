<?php

namespace App\Tests\Controller;

use App\Controller\ActivationController;
use App\Model\LineupRules;
use App\Repository\ActivationRepository;
use App\Service\ActivationService;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AllowMockObjectsWithoutExpectations]
class ActivationControllerTest extends TestCase
{
    private const LEGAL = [
        'HC' => ['1'], 'QB' => ['2'], 'RB' => ['3', '4'], 'WR' => ['5', '6'], 'TE' => ['7'],
        'K' => ['8'], 'OL' => ['9'], 'DL' => ['10', '11'], 'LB' => ['12', '13'], 'DB' => ['14', '15'],
    ];

    public function testLoggedOutVisitorsGetTheLoginPageNotAForm(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('buildSubmitView');

        $controller = $this->makeController(loggedIn: false, service: $service);
        $controller->submit(Request::create('/activations/submit'));

        $this->assertSame('activations/submit.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['loggedIn']);
    }

    public function testLoggedOutPostsAreRefusedWithoutTouchingTheDatabase(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('save');

        $controller = $this->makeController(loggedIn: false, service: $service);
        $response = $controller->save(Request::create('/activations/submit', 'POST', self::LEGAL));

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testAPostWithoutAValidCsrfTokenIsRejected(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('save');

        $controller = $this->makeController(service: $service, csrfValid: false);

        $this->expectException(AccessDeniedException::class);
        $controller->save(Request::create('/activations/submit', 'POST', self::LEGAL));
    }

    public function testTheTeamComesFromTheSessionNotTheRequest(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->once())->method('save')
            ->with(2026, 3, 6, $this->anything(), null)
            ->willReturn([]);

        $controller = $this->makeController(service: $service);
        $controller->save(Request::create('/activations/submit', 'POST',
            self::LEGAL + ['week' => '3', 'teamid' => '99', 'season' => '1999']));
    }

    public function testAnOffSeasonCurrentWeekDefaultsTheFormToWeekOne(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->expects($this->once())->method('buildSubmitView')
            ->with(2026, 1, 6, null)
            ->willReturn(['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null]);

        $weeks = $this->createStub(SeasonWeekService::class);
        $weeks->method('getCurrentSeason')->willReturn(2026);
        $weeks->method('getCurrentWeek')->willReturn(0);

        $controller = $this->makeController(service: $service, weeks: $weeks);
        $controller->submit(Request::create('/activations/submit'));
    }

    public function testAHandCraftedWeekZeroQueryStillLandsOnWeekOne(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->expects($this->once())->method('buildSubmitView')
            ->with(2026, 1, 6, null)
            ->willReturn(['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null]);

        $controller = $this->makeController(service: $service);
        $controller->submit(Request::create('/activations/submit', 'GET', ['week' => '0']));
    }

    public function testAPostForWeekZeroIsRejectedWithoutTouchingTheService(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('save');

        $controller = $this->makeController(service: $service);
        $response = $controller->save(Request::create('/activations/submit', 'POST', self::LEGAL + ['week' => '0']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['activations_submit', []], $controller->redirectedTo);
        $this->assertSame('error', $controller->flashed[0]);
    }

    public function testAValidLineupFlashesAndRedirectsToTheLineupView(): void
    {
        $service = $this->createStub(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->method('save')->willReturn([]);

        $controller = $this->makeController(service: $service);
        $response = $controller->save(Request::create('/activations/submit', 'POST', self::LEGAL + ['week' => '3']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['activations', ['season' => 2026, 'week' => 3]], $controller->redirectedTo);
        $this->assertSame('success', $controller->flashed[0]);
    }

    public function testARejectedLineupRerendersWithTheErrorsAndTheSubmittedTicks(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->method('save')->willReturn(['You must activate at least 2 WRs']);

        $submitted = null;
        $service->method('buildSubmitView')->willReturnCallback(
            function (int $s, int $w, int $t, ?array $view) use (&$submitted) {
                $submitted = $view;

                return ['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null];
            }
        );

        $lineup = self::LEGAL;
        $lineup['WR'] = ['5'];

        $controller = $this->makeController(service: $service);
        $response = $controller->save(Request::create('/activations/submit', 'POST', $lineup));

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(['You must activate at least 2 WRs'], $controller->renderedParams['errors']);
        $this->assertSame(['5'], $submitted['WR'], 'the owner keeps the boxes they ticked');
        $this->assertSame(['3', '4'], $submitted['RB']);
        $this->assertNull($controller->redirectedTo);
    }

    public function testTheActingHeadCoachOnlyCountsWhenItsBoxIsTicked(): void
    {
        $positions = LineupRules::defaults()->positions();

        $off = ActivationController::readSelections(
            Request::create('/activations/submit', 'POST', ['actHCid' => '99']),
            $positions
        );
        $this->assertNull($off['actingHcId']);

        $on = ActivationController::readSelections(
            Request::create('/activations/submit', 'POST', ['actHC' => 'on', 'actHCid' => '99']),
            $positions
        );
        $this->assertSame(99, $on['actingHcId']);
    }

    public function testAScalarPostedWhereAListBelongsDoesNotBlowUp(): void
    {
        $selections = ActivationController::readSelections(
            Request::create('/activations/submit', 'POST', ['RB' => '3']),
            LineupRules::defaults()->positions()
        );

        $this->assertSame(['3'], $selections['selections']['RB']);
        $this->assertSame([], $selections['selections']['WR']);
    }

    public function testRetiredGameplanFieldsArePassedOverEntirely(): void
    {
        $selections = ActivationController::readSelections(
            Request::create('/activations/submit', 'POST', self::LEGAL + ['myGP' => '1', 'oppGP' => '2']),
            LineupRules::defaults()->positions()
        );

        $this->assertSame(array_keys(self::LEGAL), array_keys($selections['selections']));
        $this->assertArrayNotHasKey('myGP', $selections['view']);
        $this->assertArrayNotHasKey('oppGP', $selections['view']);
    }

    public function testTheLineupViewPairsEachGamesTwoTeams(): void
    {
        $repo = $this->createStub(ActivationRepository::class);
        $repo->method('getCurrentActivations')->willReturn([
            $this->row(10, 1, 'Alpha', 'QB', 'Ann', 'Arm'),
            $this->row(10, 1, 'Alpha', 'RB', 'Abe', 'Ash'),
            $this->row(10, 2, 'Beta', 'QB', 'Bo', 'Bly'),
            $this->row(11, 3, 'Gamma', null, null, null),
            $this->row(11, 4, 'Delta', 'K', 'Dee', 'Dot'),
        ]);
        $repo->method('getAllWeeks')->willReturn([['week' => 1, 'weekname' => 'Week 1']]);

        $controller = $this->makeController(repo: $repo);
        $controller->index(Request::create('/activations', 'GET', ['season' => '2025', 'week' => '2']));

        $matchups = $controller->renderedParams['matchups'];
        $this->assertCount(2, $matchups);
        $this->assertSame(['Alpha', 'Beta'], array_column($matchups[0]['teams'], 'name'));
        $this->assertCount(2, $matchups[0]['teams'][0]['players']);
        // A team with no activations still gets a card, just an empty one
        $this->assertSame('Gamma', $matchups[1]['teams'][0]['name']);
        $this->assertSame([], $matchups[1]['teams'][0]['players']);
    }

    public function testTheLineupViewMarksLockedPlayersAtTheFiveMinuteThreshold(): void
    {
        $kickoff = time() + 20 * 60;
        $repo = $this->createStub(ActivationRepository::class);
        $repo->method('getCurrentActivations')->willReturn([
            $this->row(10, 1, 'Alpha', 'QB', 'Ann', 'Arm', $kickoff),
            $this->row(10, 1, 'Alpha', 'RB', 'Abe', 'Ash', time() - 60),
        ]);
        $repo->method('getAllWeeks')->willReturn([['week' => 1, 'weekname' => 'Week 1']]);

        $controller = $this->makeController(repo: $repo);
        $controller->index(Request::create('/activations'));

        $players = $controller->renderedParams['matchups'][0]['teams'][0]['players'];
        $locked = array_column($players, 'locked', 'pos');
        // Legacy starred anything inside 30 minutes; five is the rule now
        $this->assertFalse($locked['QB']);
        $this->assertTrue($locked['RB']);
    }

    private function row(
        int $gameId,
        int $teamId,
        string $team,
        ?string $pos,
        ?string $first,
        ?string $last,
        ?int $kickoffTs = null
    ): array {
        return [
            'gameid' => $gameId, 'teamA' => 1, 'teamB' => 2, 'teamid' => $teamId, 'name' => $team,
            'pos' => $pos, 'firstname' => $first, 'lastname' => $last, 'playerid' => 1,
            'nflteamid' => 'SEA', 'kickoff' => $kickoffTs === null ? null : '2025-11-09 13:00:00',
            'kickoffTs' => $kickoffTs, 'homeTeam' => 'SEA', 'roadTeam' => 'ARI',
            'status' => null, 'details' => null, 'ir' => null,
        ];
    }

    private function makeController(
        bool $loggedIn = true,
        ?ActivationService $service = null,
        ?ActivationRepository $repo = null,
        bool $csrfValid = true,
        ?SeasonWeekService $weeks = null
    ): ActivationController {
        $service ??= $this->defaultService();

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn(6);

        if ($weeks === null) {
            $weeks = $this->createStub(SeasonWeekService::class);
            $weeks->method('getCurrentSeason')->willReturn(2026);
            $weeks->method('getCurrentWeek')->willReturn(1);
        }

        return new class($service, $repo ?? $this->createStub(ActivationRepository::class), $auth, $weeks, $csrfValid)
            extends ActivationController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;
            public ?array $flashed = null;

            public function __construct(
                ActivationService $service,
                ActivationRepository $repo,
                AuthenticationService $auth,
                SeasonWeekService $weeks,
                private readonly bool $csrfValid
            ) {
                parent::__construct($service, $repo, $auth, $weeks);
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

    private function defaultService(): ActivationService
    {
        $service = $this->createStub(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->method('save')->willReturn([]);
        $service->method('buildSubmitView')->willReturn(
            ['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null]
        );
        $service->method('lockStateFor')->willReturnCallback(
            fn (array $player, \DateTimeImmutable $now) => $player['kickoffTs'] !== null
                && $now->getTimestamp() > $player['kickoffTs'] - 300
        );

        return $service;
    }
}
