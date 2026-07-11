<?php

namespace App\Tests\Controller;

use App\Controller\RosterMoveController;
use App\Service\AuthenticationService;
use App\Service\RosterMoveService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AllowMockObjectsWithoutExpectations]
class RosterMoveControllerTest extends TestCase
{
    // ---- GET /transactions/list ----

    public function testListAnonymousRendersLoginPromptWithoutSearching(): void
    {
        $service = $this->createMock(RosterMoveService::class);
        $service->expects($this->never())->method('searchPlayers');

        $controller = $this->makeController($service, loggedIn: false);
        $controller->list(new Request());

        $this->assertSame('transactions/list.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['loggedIn']);
    }

    public function testListWithoutSubmitShowsTheBareForm(): void
    {
        $service = $this->createMock(RosterMoveService::class);
        $service->expects($this->never())->method('searchPlayers');

        $controller = $this->makeController($service, loggedIn: true);
        $controller->list(new Request());

        $this->assertNull($controller->renderedParams['results']);
        $this->assertSame('ANY', $controller->renderedParams['criteria']['team']);
    }

    public function testListSubmitRunsTheSearchWithTheCriteria(): void
    {
        $rows = [['teamname' => 'Available', 'lastname' => 'Kaline', 'firstname' => 'Al', 'pos' => 'WR', 'team' => 'DET', 'playerid' => 7]];
        $service = $this->createMock(RosterMoveService::class);
        $service->expects($this->once())->method('searchPlayers')->with([
            'last' => 'Ka', 'first' => '', 'position' => 'WR',
            'team' => 'ANY', 'available' => 'available', 'order' => 'lastname',
        ])->willReturn($rows);

        $controller = $this->makeController($service, loggedIn: true);
        $controller->list(new Request(query: [
            'Last' => 'Ka', 'Position' => 'WR', 'Available' => 'available', 'submit' => 'List Players',
        ]));

        $this->assertSame($rows, $controller->renderedParams['results']);
    }

    // ---- POST /transactions/confirm ----

    public function testConfirmAnonymousIs401(): void
    {
        $service = $this->createMock(RosterMoveService::class);
        $service->expects($this->never())->method('executeMoves');

        $controller = $this->makeController($service, loggedIn: false);
        $response = $controller->confirm(Request::create('/transactions/confirm', 'POST'));

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testConfirmInvalidCsrfIsDenied(): void
    {
        $service = $this->createMock(RosterMoveService::class);
        $service->expects($this->never())->method('executeMoves');

        $controller = $this->makeController($service, loggedIn: true, csrfValid: false);

        $this->expectException(AccessDeniedException::class);
        $controller->confirm(Request::create('/transactions/confirm', 'POST', ['_token' => 'bad']));
    }

    public function testArrivalFromListBuildsThePreviewFromPickFields(): void
    {
        $service = $this->stubbedPreviewService();
        $service->expects($this->once())->method('getPlayersInfo')->with([50, 60])->willReturn([
            ['playerid' => 50, 'lastname' => 'Kaline', 'firstname' => 'Al', 'team' => 'DET', 'pos' => 'WR'],
            ['playerid' => 60, 'lastname' => 'Jackson', 'firstname' => 'Bo', 'team' => 'LV', 'pos' => 'RB'],
        ]);
        $service->expects($this->never())->method('executeMoves');

        $controller = $this->makeController($service, loggedIn: true, teamNum: 2);
        $controller->confirm(Request::create('/transactions/confirm', 'POST', [
            '_token' => 'ok', 'pick50' => '50', 'pick60' => '60', 'pick70' => '',
            'submit' => 'Perform Transactions',
        ]));

        $this->assertSame('transactions/confirm.html.twig', $controller->renderedView);
        $this->assertSame([], $controller->renderedParams['errors']);
        $this->assertCount(2, $controller->renderedParams['pickups']);
    }

    public function testWaiverPeriodMarksPickupsWaiverBoundWithDefaultPriorities(): void
    {
        $service = $this->stubbedPreviewService(isWaiver: true, existingPicks: [
            ['playerid' => 40, 'lastname' => 'Young', 'firstname' => 'Cy', 'team' => 'CLE', 'pos' => 'QB', 'priority' => 1],
        ]);
        $service->method('getPlayersInfo')->willReturn([
            ['playerid' => 50, 'lastname' => 'Kaline', 'firstname' => 'Al', 'team' => 'DET', 'pos' => 'WR'],
        ]);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 2);
        $controller->confirm(Request::create('/transactions/confirm', 'POST', [
            '_token' => 'ok', 'pick50' => '50', 'submit' => 'Perform Transactions',
        ]));

        $params = $controller->renderedParams;
        $this->assertTrue($params['pickups'][0]['isWaive']);
        // slots in after the existing pick
        $this->assertSame(2, $params['pickups'][0]['defaultPriority']);
        $this->assertSame(2, $params['waiverCount']);
        $this->assertTrue($params['displayWaiver']);
    }

    public function testConfirmSubmitParsesFieldsAndExecutes(): void
    {
        $service = $this->createMock(RosterMoveService::class);
        $service->expects($this->once())->method('executeMoves')
            ->with(2, [50], [10, 20], [1 => 60], true)
            ->willReturn([]);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 2);
        $response = $controller->confirm(Request::create('/transactions/confirm', 'POST', [
            '_token' => 'ok',
            'submit' => 'Confirm',
            'pick50' => 'y',
            'pick55' => 'n',
            'keep10' => 'n',
            'keep11' => 'y',
            'injr20' => 'n',
            'prio60' => '1',
            'prio61' => 'n',
        ]));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['transactions_history', []], $controller->redirectedTo);
    }

    public function testConfirmValidationErrorsReshowThePreview(): void
    {
        $service = $this->stubbedPreviewService();
        $service->method('executeMoves')->willReturn(['That would give you 27 players on your roster!!  You must drop someone!!']);
        $service->method('getPlayersInfo')->willReturn([
            ['playerid' => 50, 'lastname' => 'Kaline', 'firstname' => 'Al', 'team' => 'DET', 'pos' => 'WR'],
        ]);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 2);
        $response = $controller->confirm(Request::create('/transactions/confirm', 'POST', [
            '_token' => 'ok', 'submit' => 'Confirm', 'pick50' => 'y',
        ]));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('transactions/confirm.html.twig', $controller->renderedView);
        $this->assertCount(1, $controller->renderedParams['errors']);
        $this->assertCount(1, $controller->renderedParams['pickups']);
    }

    // ---- helpers ----

    private function stubbedPreviewService(bool $isWaiver = false, array $existingPicks = []): RosterMoveService
    {
        $service = $this->createMock(RosterMoveService::class);
        $service->method('getWaiverContext')->willReturn(['isWaiver' => $isWaiver, 'season' => 2025, 'week' => 5]);
        $service->method('getExistingWaiverPicks')->willReturn($existingPicks);
        $service->method('getWaiverEligiblePlayerIds')->willReturn([]);
        $service->method('getTeamCounts')->willReturn(['total' => 20, 'ir' => 1, 'active' => 19, 'ptsLeft' => 30]);
        $service->method('getCurrentRoster')->willReturn([]);

        return $service;
    }

    private function makeController(
        RosterMoveService $service,
        bool $loggedIn,
        ?int $teamNum = null,
        bool $csrfValid = true
    ): RosterMoveController {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        return new class($service, $auth, $csrfValid) extends RosterMoveController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;

            public function __construct($service, $auth, private readonly bool $csrfValid)
            {
                parent::__construct($service, $auth);
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

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }
        };
    }
}
