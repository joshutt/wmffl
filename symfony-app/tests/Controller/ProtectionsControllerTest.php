<?php

namespace App\Tests\Controller;

use App\Controller\ProtectionsController;
use App\Service\AuthenticationService;
use App\Service\ProtectionsService;
use App\Service\SeasonWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class ProtectionsControllerTest extends TestCase
{
    private const POINTS = ['totalPts' => 55, 'protectionPts' => 12, 'paid' => true];

    // ---- GET /transactions/protections ----

    public function testFormAnonymousRendersLoginPrompt(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->expects($this->never())->method('getRosterWithCosts');

        $controller = $this->makeController($service, loggedIn: false);
        $controller->form();

        $this->assertSame('transactions/protections.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['loggedIn']);
    }

    public function testFormLoadsRosterPointsAndDeadlineForTheMember(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->method('getDeadline')->willReturn(new \DateTimeImmutable('2030-08-16 23:59'));
        $service->method('isDeadlinePassed')->willReturn(false);
        $service->expects($this->once())->method('getPointsSummary')->with(4, 2026)->willReturn(self::POINTS);
        $service->expects($this->once())->method('getRosterWithCosts')->with(4, 2026)->willReturn([['playerid' => 7]]);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $controller->form();

        $params = $controller->renderedParams;
        $this->assertTrue($params['loggedIn']);
        $this->assertFalse($params['deadlinePassed']);
        $this->assertSame(self::POINTS, $params['points']);
        $this->assertSame([['playerid' => 7]], $params['roster']);
    }

    // ---- POST /transactions/protections/save ----

    public function testSaveAnonymousIs401AndSavesNothing(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->expects($this->never())->method('saveProtections');

        $controller = $this->makeController($service, loggedIn: false);
        $controller->save($this->postRequest());

        $this->assertSame('not_logged_in', $controller->renderedParams['error']);
    }

    public function testSaveInvalidCsrfIs403(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->expects($this->never())->method('saveProtections');

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4, csrfValid: false);
        $response = $controller->save($this->postRequest());

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testSaveAfterDeadlineIsRejected(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->method('isDeadlinePassed')->willReturn(true);
        $service->expects($this->never())->method('saveProtections');

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $controller->save($this->postRequest());

        $this->assertSame('deadline', $controller->renderedParams['error']);
    }

    public function testSaveWhileUnpaidIsRejected(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->method('isDeadlinePassed')->willReturn(false);
        $service->method('getPointsSummary')->willReturn(['totalPts' => 55, 'protectionPts' => 0, 'paid' => false]);
        $service->expects($this->never())->method('saveProtections');

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $controller->save($this->postRequest());

        $this->assertSame('unpaid', $controller->renderedParams['error']);
    }

    public function testSaveOverBudgetShowsSpentAndAllowed(): void
    {
        $service = $this->createMock(ProtectionsService::class);
        $service->method('isDeadlinePassed')->willReturn(false);
        $service->method('getPointsSummary')->willReturn(self::POINTS);
        $service->method('saveProtections')->willReturn(['ok' => false, 'totalCost' => 60, 'allowed' => 55]);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $controller->save($this->postRequest());

        $this->assertSame('over_budget', $controller->renderedParams['error']);
        $this->assertSame(60, $controller->renderedParams['spent']);
        $this->assertSame(55, $controller->renderedParams['allowed']);
    }

    public function testSaveHappyPathPassesSelectionAndRendersSavedList(): void
    {
        $saved = [['player' => 'Al Kaline', 'pos' => 'WR', 'team' => 'DET', 'cost' => 3]];
        $service = $this->createMock(ProtectionsService::class);
        $service->method('isDeadlinePassed')->willReturn(false);
        $service->method('getPointsSummary')->willReturn(self::POINTS);
        $service->expects($this->once())->method('saveProtections')->with(4, 2026, ['7', '9'])
            ->willReturn(['ok' => true, 'totalCost' => 8, 'allowed' => 55]);
        $service->method('getSavedProtections')->willReturn($saved);

        $controller = $this->makeController($service, loggedIn: true, teamNum: 4);
        $controller->save($this->postRequest(['protect' => ['7', '9']]));

        $this->assertSame('transactions/protections_saved.html.twig', $controller->renderedView);
        $this->assertNull($controller->renderedParams['error']);
        $this->assertSame($saved, $controller->renderedParams['saved']);
        $this->assertSame(8, $controller->renderedParams['totalCost']);
    }

    // ---- helpers ----

    private function postRequest(array $extra = []): Request
    {
        return Request::create('/transactions/protections/save', 'POST', $extra + ['_token' => 'tok']);
    }

    private function makeController(
        ProtectionsService $service,
        bool $loggedIn,
        ?int $teamNum = null,
        bool $csrfValid = true
    ): ProtectionsController {
        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2026);

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamNum);

        return new class($service, $seasonWeek, $auth, $csrfValid) extends ProtectionsController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            public function __construct($service, $seasonWeek, $auth, private readonly bool $csrfValid)
            {
                parent::__construct($service, $seasonWeek, $auth);
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return $response ?? new Response();
            }

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }
        };
    }
}
