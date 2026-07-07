<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminBecomeController;
use App\Service\AuthenticationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminBecomeControllerTest extends TestCase
{
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

    public function testBecomeAsRedirectsToDashboardWhenTeamNotFound(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: false);

        $request  = new Request(request: ['teamId' => '99']);
        $response = $controller->becomeAs($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_dashboard', $response->getTargetUrl());
    }

    public function testBecomeAsAddsErrorFlashWhenTeamNotFound(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: false);

        $request = new Request(request: ['teamId' => '99']);
        $controller->becomeAs($request, $auth, $em);

        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    public function testBecomeAsRejectsInvalidCsrfToken(): void
    {
        $user = ['teamid' => 5, 'name' => 'Team X', 'username' => 'teamx', 'userid' => 10];
        [$controller, $auth, $em] = $this->makeController(commissioner: true, user: $user);
        $controller->csrfValid = false;

        $auth->expects($this->never())->method('becomeTeam');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->becomeAs(new Request(request: ['teamId' => '5']), $auth, $em);
    }

    // ---- Helpers ----

    private Connection $conn;

    private function makeController(bool $commissioner, array|false $user = false): array
    {
        $controller = new class extends AdminBecomeController {
            public bool $csrfValid = true;

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

            public array $flashes = [];

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

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->conn);

        return [$controller, $auth, $em];
    }
}
