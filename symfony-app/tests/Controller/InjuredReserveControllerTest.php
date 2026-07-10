<?php

namespace App\Tests\Controller;

use App\Controller\InjuredReserveController;
use App\Service\AuthenticationService;
use App\Service\InjuredReserveService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class InjuredReserveControllerTest extends TestCase
{
    // ---- GET /transactions/ir ----

    public function testIndexAnonymousRendersLoginPromptWithoutQueries(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->never())->method('getEligiblePlayers');
        $service->expects($this->never())->method('getCurrentIrPlayers');

        $controller = $this->makeController($service, loggedIn: false);
        $controller->index();

        $this->assertSame('transactions/ir.html.twig', $controller->renderedView);
        $this->assertNull($controller->renderedParams['eligible']);
        $this->assertNull($controller->renderedParams['current']);
    }

    public function testIndexLoggedInLoadsBothListsForTheMembersTeam(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->once())->method('getEligiblePlayers')->with(4)->willReturn([['playerid' => 1]]);
        $service->expects($this->once())->method('getCurrentIrPlayers')->with(4)->willReturn([['playerid' => 2]]);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $controller->index();

        $this->assertSame([['playerid' => 1]], $controller->renderedParams['eligible']);
        $this->assertSame([['playerid' => 2]], $controller->renderedParams['current']);
    }

    // ---- POST /transactions/ir/update ----

    public function testUpdateRejectsAnonymousWith401(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->never())->method('addPlayerToIr');

        $controller = $this->makeController($service, loggedIn: false);
        $response = $controller->update(Request::create('/transactions/ir/update', 'POST'));

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testUpdateRejectsInvalidCsrfWith403(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->never())->method('addPlayerToIr');

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4, csrfValid: false);
        $response = $controller->update(Request::create('/transactions/ir/update', 'POST', [
            'method' => 'Add', 'playerid' => '77', '_token' => 'bad',
        ]));

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testUpdateAddReturnsLegacyShapedSuccessMessage(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->once())->method('addPlayerToIr')->with(4, 77)->willReturn(true);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $response = $controller->update(Request::create('/transactions/ir/update', 'POST', [
            'method' => 'Add', 'playerid' => '77', '_token' => 'ok',
        ]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('"Player 77 Added to IR"', $response->getContent());
    }

    public function testUpdateAddFailureReturnsUnableMessage(): void
    {
        $service = $this->createStub(InjuredReserveService::class);
        $service->method('addPlayerToIr')->willReturn(false);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $response = $controller->update(Request::create('/transactions/ir/update', 'POST', [
            'method' => 'Add', 'playerid' => '77', '_token' => 'ok',
        ]));

        $this->assertSame('"Unable to add 77 to IR"', $response->getContent());
    }

    public function testUpdateRemoveDelegatesToTheService(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->once())->method('removePlayerFromIr')->with(4, 88)->willReturn(true);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $response = $controller->update(Request::create('/transactions/ir/update', 'POST', [
            'method' => 'Remove', 'playerid' => '88', '_token' => 'ok',
        ]));

        $this->assertSame('"Player 88 removed from IR"', $response->getContent());
    }

    public function testUpdateUnknownMethodIsA400(): void
    {
        $service = $this->createMock(InjuredReserveService::class);
        $service->expects($this->never())->method('addPlayerToIr');
        $service->expects($this->never())->method('removePlayerFromIr');

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $response = $controller->update(Request::create('/transactions/ir/update', 'POST', [
            'method' => 'Explode', 'playerid' => '88', '_token' => 'ok',
        ]));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    // ---- helpers ----

    private function makeController(
        InjuredReserveService $service,
        bool $loggedIn,
        ?int $teamNum = null,
        bool $csrfValid = true
    ): InjuredReserveController {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        return new class($service, $auth, $csrfValid) extends InjuredReserveController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            public function __construct($service, $auth, private readonly bool $csrfValid)
            {
                parent::__construct($service, $auth);
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }
        };
    }
}
