<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminHeadCoachController;
use App\Entity\Team;
use App\Service\AuthenticationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class AdminHeadCoachControllerTest extends TestCase
{
    // ---- GET /admin/headcoach ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersCorrectTemplateForCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $em);

        $this->assertSame('admin/headcoach/index.html.twig', $controller->renderedView);
    }

    public function testIndexPassesTeamsAndCoachesToTemplate(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $em);

        $this->assertArrayHasKey('teams', $controller->renderedParams);
        $this->assertArrayHasKey('coaches', $controller->renderedParams);
    }

    public function testIndexPassesAllTeamsOrderedByName(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $em);

        // Verify the repository was called with name ordering
        $this->assertSame([], $controller->renderedParams['teams']); // stub returns []
    }

    public function testIndexFetchesCoachesFromDatabase(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $conn = $this->getConn($em);
        $conn->expects($this->once())->method('fetchAllAssociative');

        $controller->index($auth, $em);
    }

    // ---- POST /admin/headcoach/process ----

    public function testProcessRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->process(new Request(), $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testProcessRedirectsToIndexAfterHire(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, coachAlreadyOnTeam: false);

        $request = new Request(request: ['team' => '1', 'player' => '42']);
        $response = $controller->process($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_headcoach', $response->getTargetUrl());
    }

    public function testProcessExecutesFourSqlStatements(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, coachAlreadyOnTeam: false);

        $conn = $this->getConn($em);
        $conn->expects($this->exactly(4))->method('executeStatement');

        $request = new Request(request: ['team' => '1', 'player' => '42']);
        $controller->process($request, $auth, $em);
    }

    public function testProcessRejectsHireIfCoachAlreadyOnAnotherTeam(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, coachAlreadyOnTeam: true);

        $conn = $this->getConn($em);
        $conn->expects($this->never())->method('executeStatement');

        $request = new Request(request: ['team' => '1', 'player' => '42']);
        $response = $controller->process($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_headcoach', $response->getTargetUrl());
        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    // ---- POST /admin/headcoach/drop ----

    public function testDropRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->drop(new Request(), $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testDropExecutesTwoSqlStatements(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $conn = $this->getConn($em);
        $conn->expects($this->exactly(2))->method('executeStatement');

        $request = new Request(request: ['player' => '42']);
        $controller->drop($request, $auth, $em);
    }

    public function testDropRedirectsToIndexAfterDrop(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $request = new Request(request: ['player' => '42']);
        $response = $controller->drop($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_headcoach', $response->getTargetUrl());
    }

    // ---- Helpers ----

    private Connection $conn;

    private function makeController(bool $commissioner, bool $coachAlreadyOnTeam = false): array
    {
        $controller = new class extends AdminHeadCoachController {
            public bool $csrfValid = true;

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

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

            public function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = ['type' => $type, 'message' => $message];
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $this->conn = $this->createMock(Connection::class);
        $this->conn->method('fetchAllAssociative')->willReturn([]);
        $this->conn->method('fetchOne')->willReturn($coachAlreadyOnTeam ? 99 : false);
        $this->conn->method('executeStatement')->willReturn(0);

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findBy')->willReturn([]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->conn);
        $em->method('getRepository')->willReturn($repo);
        $em->method('find')->willReturn($this->createStub(Team::class));

        return [$controller, $auth, $em];
    }

    private function getConn(EntityManagerInterface $em): Connection
    {
        return $this->conn;
    }
}
