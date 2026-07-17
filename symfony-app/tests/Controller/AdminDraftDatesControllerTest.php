<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminDraftDatesController;
use App\Service\AuthenticationService;
use App\Service\DraftScheduleService;
use App\Service\SeasonWeekService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class AdminDraftDatesControllerTest extends TestCase
{
    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersCorrectTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService);

        $this->assertSame('admin/draftdates/index.html.twig', $controller->renderedView);
    }

    public function testIndexDefaultsToCurrentSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService);

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testIndexUsesExplicitSeasonWhenProvided(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2023);

        $this->assertSame(2023, $controller->renderedParams['season']);
    }

    public function testIndexAggregatesYesNoCountsPerDate(): void
    {
        $rows = [
            ['name' => 'Team A', 'date' => '2025-07-12', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-12', 'attend' => 'N'],
            ['name' => 'Team A', 'date' => '2025-07-19', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-19', 'attend' => 'Y'],
        ];

        [$controller, $auth, $seasonWeek, $em] = $this->makeController(
            commissioner: true,
            dateRows: $rows
        );

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2025);

        $dates = $controller->renderedParams['dates'];
        $this->assertSame(1, $dates['2025-07-12']['yes']);
        $this->assertSame(1, $dates['2025-07-12']['no']);
        $this->assertSame(2, $dates['2025-07-19']['yes']);
        $this->assertSame(0, $dates['2025-07-19']['no']);
    }

    public function testIndexComputesMaxYes(): void
    {
        $rows = [
            ['name' => 'Team A', 'date' => '2025-07-12', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-12', 'attend' => 'N'],
            ['name' => 'Team A', 'date' => '2025-07-19', 'attend' => 'Y'],
            ['name' => 'Team B', 'date' => '2025-07-19', 'attend' => 'Y'],
        ];

        [$controller, $auth, $seasonWeek, $em] = $this->makeController(
            commissioner: true,
            dateRows: $rows
        );

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2025);

        $this->assertSame(2, $controller->renderedParams['maxYes']);
    }

    public function testIndexPassesNoVoteTeamsToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(
            commissioner: true,
            noVoteTeams: ['Team X', 'Team Y']
        );

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2025);

        $this->assertSame(['Team X', 'Team Y'], $controller->renderedParams['noVote']);
    }

    public function testIndexQueriesWithCorrectSeasonCutoff(): void
    {
        [$controller, $auth, $seasonWeek, $em, $conn] = $this->makeController(commissioner: true);

        $conn->expects($this->once())
            ->method('fetchAllAssociative')
            ->with(
                $this->stringContains('draftdate'),
                $this->equalTo(['cutoff' => '2024-01-01'])
            )
            ->willReturn([]);

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2024);
    }

    public function testIndexQueriesNoVoteWithCorrectSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em, $conn] = $this->makeController(commissioner: true);

        $conn->expects($this->once())
            ->method('fetchFirstColumn')
            ->with(
                $this->stringContains('draftvote'),
                $this->equalTo(['season' => 2024])
            )
            ->willReturn([]);

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2024);
    }

    // ---- Schedule builder ----

    public function testIndexWithoutRangeSkipsCandidates(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->index(new Request(), $auth, $seasonWeek, $em, $this->scheduleService, 2026);

        $this->assertNull($controller->renderedParams['candidates']);
    }

    public function testIndexWithValidRangeRendersCandidates(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $candidates = [
            ['date' => new \DateTimeImmutable('2026-08-01'), 'checked' => true],
            ['date' => new \DateTimeImmutable('2026-08-03'), 'checked' => false],
        ];
        $this->scheduleService->method('candidateDates')->willReturn($candidates);
        $this->scheduleService->method('existingDates')->willReturn([]);

        $request = new Request(query: ['first' => '2026-08-01', 'last' => '2026-08-03']);
        $controller->index($request, $auth, $seasonWeek, $em, $this->scheduleService, 2026);

        $this->assertSame($candidates, $controller->renderedParams['candidates']);
    }

    public function testIndexPreChecksExistingScheduleOverWeekendDefault(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $this->scheduleService->method('candidateDates')->willReturn([
            ['date' => new \DateTimeImmutable('2026-08-01'), 'checked' => true],  // Sat
            ['date' => new \DateTimeImmutable('2026-08-03'), 'checked' => false], // Mon
        ]);
        $this->scheduleService->method('existingDates')->willReturn(['2026-08-03']);

        $request = new Request(query: ['first' => '2026-08-01', 'last' => '2026-08-03']);
        $controller->index($request, $auth, $seasonWeek, $em, $this->scheduleService, 2026);

        $checked = array_map(fn($c) => $c['checked'], $controller->renderedParams['candidates']);
        $this->assertSame([false, true], $checked);
    }

    public function testIndexRejectsRangeOutsideSeasonWindow(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $request = new Request(query: ['first' => '2026-06-01', 'last' => '2026-08-01']);
        $controller->index($request, $auth, $seasonWeek, $em, $this->scheduleService, 2026);

        $this->assertNull($controller->renderedParams['candidates']);
        $this->assertNotEmpty($controller->flashes['error']);
    }

    public function testScheduleRejectsEmptySelection(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $this->scheduleService->expects($this->never())->method('applySchedule');

        $response = $controller->schedule(2026, new Request(), $auth, $this->scheduleService);

        $this->assertNotEmpty($controller->flashes['error']);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testSchedulePassesSelectedDatesToService(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $this->scheduleService->expects($this->once())->method('applySchedule')
            ->with(2026, ['2026-08-01', '2026-08-08'])
            ->willReturn(['createVotes' => 12, 'createDates' => 24, 'deleteDates' => 1]);

        $request = new Request(request: ['dates' => ['2026-08-01', '2026-08-08']]);
        $response = $controller->schedule(2026, $request, $auth, $this->scheduleService);

        $this->assertNotEmpty($controller->flashes['success']);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testScheduleFlashesWindowErrorFromService(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $this->scheduleService->method('applySchedule')
            ->willThrowException(new \InvalidArgumentException('outside the window'));

        $request = new Request(request: ['dates' => ['2026-06-30']]);
        $controller->schedule(2026, $request, $auth, $this->scheduleService);

        $this->assertSame(['outside the window'], $controller->flashes['error']);
    }

    // ---- Helpers ----

    private Connection $conn;
    private DraftScheduleService $scheduleService;

    private function makeController(
        bool $commissioner,
        array $dateRows = [],
        array $noVoteTeams = []
    ): array {
        $controller = new class extends AdminDraftDatesController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public array $flashes = [];

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashes[$type][] = $message;
            }

            protected function assertCsrfToken(Request $request, string $id): void
            {
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $this->conn = $this->createMock(Connection::class);
        $this->conn->method('fetchAllAssociative')->willReturn($dateRows);
        $this->conn->method('fetchFirstColumn')->willReturn($noVoteTeams);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->conn);

        $this->scheduleService = $this->createMock(DraftScheduleService::class);

        return [$controller, $auth, $seasonWeek, $em, $this->conn];
    }
}
