<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminActivationController;
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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminActivationControllerTest extends TestCase
{
    private const LEGAL = [
        'HC' => ['1'], 'QB' => ['2'], 'RB' => ['3', '4'], 'WR' => ['5', '6'], 'TE' => ['7'],
        'K' => ['8'], 'OL' => ['9'], 'DL' => ['10', '11'], 'LB' => ['12', '13'], 'DB' => ['14', '15'],
    ];

    public function testNonCommissionersAreRedirectedAwayFromTheIndex(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('buildSubmitView');

        $controller = $this->makeController($service);
        $response = $controller->index(Request::create('/admin/activations'), $this->auth(false));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertNull($controller->renderedView);
    }

    public function testNonCommissionersCannotOpenATeamsLineup(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('buildSubmitView');

        $controller = $this->makeController($service);
        $response = $controller->edit(6, 2025, 10, $this->auth(false));

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testNonCommissionersCannotSave(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('save');

        $controller = $this->makeController($service);
        $controller->save(6, 2025, 10, Request::create('/admin/activations/6/2025/10', 'POST', self::LEGAL), $this->auth(false));
    }

    public function testASaveWithoutAValidCsrfTokenIsRejected(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('save');

        $controller = $this->makeController($service, csrfValid: false);

        $this->expectException(AccessDeniedHttpException::class);
        $controller->save(6, 2025, 10, Request::create('/admin/activations/6/2025/10', 'POST', self::LEGAL), $this->auth(true));
    }

    public function testTheIndexDefaultsAnOffSeasonCurrentWeekToWeekOne(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('buildSubmitView');

        $weeks = $this->createStub(SeasonWeekService::class);
        $weeks->method('getCurrentSeason')->willReturn(2026);
        $weeks->method('getCurrentWeek')->willReturn(0);

        $controller = $this->makeController($service, weeks: $weeks);
        $controller->index(Request::create('/admin/activations'), $this->auth(true));

        $this->assertSame(1, $controller->renderedParams['week']);
    }

    public function testCannotOpenTheOverrideFormForWeekZero(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('buildSubmitView');

        $controller = $this->makeController($service);
        $response = $controller->edit(6, 2025, 0, $this->auth(true));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['admin_activations', ['season' => 2025]], $controller->redirectedTo);
    }

    public function testCannotSaveForWeekZero(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->expects($this->never())->method('save');

        $controller = $this->makeController($service);
        $response = $controller->save(6, 2025, 0,
            Request::create('/admin/activations/6/2025/0', 'POST', self::LEGAL), $this->auth(true));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(['admin_activations', ['season' => 2025]], $controller->redirectedTo);
        $this->assertSame('error', $controller->flashed[0]);
    }

    public function testTheOverrideFormIsBuiltWithLocksBypassed(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->expects($this->once())->method('buildSubmitView')
            ->with(2025, 10, 6, null, null, true)
            ->willReturn(['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null]);

        $controller = $this->makeController($service);
        $controller->edit(6, 2025, 10, $this->auth(true));

        $this->assertSame('admin/activations/edit.html.twig', $controller->renderedView);
        $this->assertTrue($controller->renderedParams['admin']);
        $this->assertSame('Sixes', $controller->renderedParams['teamName']);
    }

    public function testSavingBypassesLocksButKeepsThePositionRules(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->expects($this->once())->method('save')
            ->with(2025, 10, 6, $this->anything(), null, false, true)
            ->willReturn([]);

        $controller = $this->makeController($service);
        $controller->save(6, 2025, 10,
            Request::create('/admin/activations/6/2025/10', 'POST', self::LEGAL), $this->auth(true));

        $this->assertSame(
            ['admin_activations_edit', ['teamId' => 6, 'season' => 2025, 'week' => 10]],
            $controller->redirectedTo
        );
        $this->assertSame('success', $controller->flashed[0]);
    }

    public function testAnIllegalLineupNeedsTheExplicitCheckbox(): void
    {
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->method('buildSubmitView')->willReturn(
            ['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null]
        );
        $service->expects($this->once())->method('save')
            ->with(2025, 10, 6, $this->anything(), null, true, true)
            ->willReturn([]);

        $controller = $this->makeController($service);
        $controller->save(6, 2025, 10, Request::create(
            '/admin/activations/6/2025/10', 'POST', self::LEGAL + ['allowIllegal' => '1']
        ), $this->auth(true));

        $this->assertStringContainsString('illegal lineup kept', $controller->flashed[1]);
    }

    public function testARejectedOverrideRerendersWithTheErrorsAndTheSubmittedTicks(): void
    {
        $submitted = null;
        $service = $this->createMock(ActivationService::class);
        $service->method('getLineupRules')->willReturn(LineupRules::defaults());
        $service->method('save')->willReturn(['You must activate exactly 2 DBs']);
        $service->method('buildSubmitView')->willReturnCallback(
            function (int $s, int $w, int $t, ?array $view) use (&$submitted) {
                $submitted = $view;

                return ['starters' => [], 'reserves' => [], 'allLock' => false, 'actingHc' => null];
            }
        );

        $lineup = self::LEGAL;
        $lineup['DB'] = ['14'];

        $controller = $this->makeController($service);
        $response = $controller->save(6, 2025, 10,
            Request::create('/admin/activations/6/2025/10', 'POST', $lineup), $this->auth(true));

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(['You must activate exactly 2 DBs'], $controller->renderedParams['errors']);
        $this->assertSame(['14'], $submitted['DB']);
        $this->assertNull($controller->redirectedTo);
    }

    private function auth(bool $commissioner): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        return $auth;
    }

    private function makeController(
        ActivationService $service,
        bool $csrfValid = true,
        ?SeasonWeekService $weeks = null
    ): AdminActivationController {
        $repo = $this->createStub(ActivationRepository::class);
        $repo->method('getTeamOptions')->willReturn([['teamid' => 6, 'name' => 'Sixes']]);
        $repo->method('getAllWeeks')->willReturn([['week' => 1, 'weekname' => 'Week 1']]);

        if ($weeks === null) {
            $weeks = $this->createStub(SeasonWeekService::class);
            $weeks->method('getCurrentSeason')->willReturn(2026);
            $weeks->method('getCurrentWeek')->willReturn(1);
        }

        return new class($service, $repo, $weeks, $csrfValid) extends AdminActivationController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;
            public ?array $flashed = null;

            public function __construct(
                ActivationService $service,
                ActivationRepository $repo,
                SeasonWeekService $weeks,
                private readonly bool $csrfValid
            ) {
                parent::__construct($service, $repo, $weeks);
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
