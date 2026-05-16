<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminBecomeController;
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
class AdminBecomeControllerTest extends TestCase
{
    // ---- GET /admin/become ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersCorrectTemplate(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $em);

        $this->assertSame('admin/become/index.html.twig', $controller->renderedView);
    }

    public function testIndexPassesTeamsToTemplate(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $controller->index($auth, $em);

        $this->assertArrayHasKey('teams', $controller->renderedParams);
    }

    // ---- POST /admin/become ----

    public function testBecomeAsRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->becomeAs(new Request(), $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testBecomeAsRedirectsToHomeOnSuccess(): void
    {
        $user = ['teamid' => 5, 'name' => 'Team X', 'username' => 'teamx', 'userid' => 10];
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: $user);

        $request  = new Request(request: ['teamId' => '5']);
        $response = $controller->becomeAs($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testBecomeAsCallsBecomeTeamWithCorrectArgs(): void
    {
        $user = ['teamid' => 5, 'name' => 'Team X', 'username' => 'teamx', 'userid' => 10];
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: $user);

        $auth->expects($this->once())
            ->method('becomeTeam')
            ->with(5, 'Team X', 'teamx', 10);

        $request = new Request(request: ['teamId' => '5']);
        $controller->becomeAs($request, $auth, $em);
    }

    public function testBecomeAsRedirectsToIndexWhenTeamNotFound(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: false);

        $request  = new Request(request: ['teamId' => '99']);
        $response = $controller->becomeAs($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_become', $response->getTargetUrl());
    }

    public function testBecomeAsAddsErrorFlashWhenTeamNotFound(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: false);

        $request = new Request(request: ['teamId' => '99']);
        $controller->becomeAs($request, $auth, $em);

        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    // ---- Helpers ----

    private Connection $conn;

    private function makeController(bool $commissioner, array|false $user = false): array
    {
        $controller = new class extends AdminBecomeController {
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

            protected function redirect(string $url, int $status = 302): RedirectResponse
            {
                return new RedirectResponse($url, $status);
            }

            public function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = ['type' => $type, 'message' => $message];
            }
        };

        $auth = $this->createMock(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $this->conn = $this->createStub(Connection::class);
        $this->conn->method('fetchAssociative')->willReturn($user);

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findBy')->willReturn([]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->conn);
        $em->method('getRepository')->willReturn($repo);

        return [$controller, $auth, $em];
    }
}
