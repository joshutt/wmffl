<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminUserController;
use App\Entity\Team;
use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
use App\Service\PasswordResetService;
use App\Service\UserEmailService;
use App\Service\UserTeamAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminUserControllerTest extends TestCase
{
    // ---- index ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $users] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $users);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexPassesAllUsersToTemplate(): void
    {
        $rows = [$this->user(1, 'alice'), $this->user(2, 'bob')];
        [$controller, $auth, $users] = $this->makeController(commissioner: true, rows: $rows);

        $controller->index($auth, $users);

        $this->assertSame($rows, $controller->renderedParams['users']);
    }

    // ---- new ----

    public function testNewRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: false);

        $response = $controller->new(new Request(), $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testNewPersistsAndRedirects(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: true);

        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $em->expects($this->once())->method('flush');
        $teamAssignment->expects($this->once())->method('assign')
            ->with($this->isInstanceOf(User::class), null);

        $request = Request::create('/admin/users/new', 'POST', [
            'username' => 'newuser', 'emails' => ['new@example.com'], 'primaryEmail' => '0', 'active' => '1', 'name' => 'New User',
        ]);
        $response = $controller->new($request, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_users', $response->getTargetUrl());
    }

    public function testNewAssignsSelectedTeam(): void
    {
        $team = $this->team(4, 'Squirrels');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            teamsById: [4 => $team]
        );

        $teamAssignment->expects($this->once())->method('assign')
            ->with($this->isInstanceOf(User::class), $team);

        $request = Request::create('/admin/users/new', 'POST', [
            'username' => 'newuser', 'emails' => ['new@example.com'], 'primaryEmail' => '0', 'active' => '1', 'teamId' => '4',
        ]);
        $controller->new($request, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);
    }

    public function testNewRejectsBlankUsername(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: true);

        $em->expects($this->never())->method('persist');

        $request = Request::create('/admin/users/new', 'POST', ['username' => '', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1']);
        $controller->new($request, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);

        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    public function testNewRejectsDuplicateUsername(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            rows: [$this->user(1, 'taken')]
        );

        $em->expects($this->never())->method('persist');

        $request = Request::create('/admin/users/new', 'POST', ['username' => 'taken', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1']);
        $controller->new($request, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);

        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    public function testNewRejectsUnknownTeamId(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: true);

        $em->expects($this->never())->method('persist');
        $teamAssignment->expects($this->never())->method('assign');

        $request = Request::create('/admin/users/new', 'POST', [
            'username' => 'newuser', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1', 'teamId' => '999',
        ]);
        $controller->new($request, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);

        $this->assertCount(1, $controller->flashes);
    }

    public function testNewRejectsInvalidCsrfToken(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: true);
        $controller->csrfValid = false;

        $em->expects($this->never())->method('persist');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->new(
            Request::create('/admin/users/new', 'POST', ['username' => 'a', 'emails' => ['b@c.com'], 'primaryEmail' => '0', 'active' => '1']),
            $auth,
            $users,
            $teams,
            $teamAssignment,
            $em,
            $emails,
            $resets
        );
    }

    // ---- edit ----

    public function testEditThrows404WhenUserMissing(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: true);

        $this->expectException(NotFoundHttpException::class);
        $controller->edit(999, new Request(), $auth, $users, $teams, $teamAssignment, $em, $emails);
    }

    public function testEditUpdatesFieldsAndRedirects(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            rows: [$existing]
        );

        $em->expects($this->once())->method('flush');
        $teamAssignment->expects($this->once())->method('assign')->with($existing, null);

        $request  = Request::create('/admin/users/1/edit', 'POST', [
            'username' => 'alice', 'emails' => ['alice@example.com'], 'primaryEmail' => '0', 'active' => '1', 'name' => 'Alice A',
        ]);
        $response = $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertSame(['alice@example.com'], $existing->getEmailAddresses());
        $this->assertSame('Alice A', $existing->getName());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_users', $response->getTargetUrl());
    }

    public function testEditAllowsKeepingOwnUsername(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            rows: [$existing]
        );

        $em->expects($this->once())->method('flush');

        $request  = Request::create('/admin/users/1/edit', 'POST', ['username' => 'alice', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1']);
        $response = $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testEditRejectsUsernameTakenByAnotherUser(): void
    {
        $existing = $this->user(1, 'alice');
        $other    = $this->user(2, 'bob');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            rows: [$existing, $other]
        );

        $em->expects($this->never())->method('flush');

        $request = Request::create('/admin/users/1/edit', 'POST', ['username' => 'bob', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1']);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertCount(1, $controller->flashes);
    }

    public function testEditRejectsInvalidCsrfToken(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            rows: [$existing]
        );
        $controller->csrfValid = false;

        $this->expectException(AccessDeniedHttpException::class);
        $controller->edit(
            1,
            Request::create('/admin/users/1/edit', 'POST', ['username' => 'alice', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1']),
            $auth,
            $users,
            $teams,
            $teamAssignment,
            $em,
            $emails
        );
    }

    public function testEditNeverExposesCommish(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(
            commissioner: true,
            rows: [$existing]
        );

        $request = Request::create('/admin/users/1/edit', 'POST', [
            'username' => 'alice', 'emails' => ['a@b.com'], 'primaryEmail' => '0', 'active' => '1', 'commish' => '1',
        ]);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertFalse($existing->isCommish(), 'commish must never be settable through this controller');
    }

    // ---- email addresses (Phase 18 item 1) ----

    public function testNewSendsSetPasswordLinkAndLeavesPasswordBlank(): void
    {
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets] = $this->makeController(commissioner: true);

        $created = null;
        $em->method('persist')->willReturnCallback(function (User $u) use (&$created) { $created = $u; });
        $resets->expects($this->once())->method('sendLink')
            ->with($this->isInstanceOf(User::class), true)->willReturn(true);

        $request = Request::create('/admin/users/new', 'POST', [
            'username' => 'newuser', 'emails' => ['one@example.com', 'two@example.com'], 'primaryEmail' => '1', 'active' => '1',
        ]);
        $controller->new($request, $auth, $users, $teams, $teamAssignment, $em, $emails, $resets);

        $this->assertSame('', $created->getPassword());
        $this->assertSame(['one@example.com', 'two@example.com'], $created->getEmailAddresses());
        $this->assertSame('two@example.com', $created->getPrimaryEmail());
    }

    public function testEditReplacesAddressListAndPrimary(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails] = $this->makeController(commissioner: true, rows: [$existing]);

        $request = Request::create('/admin/users/1/edit', 'POST', [
            'username' => 'alice', 'active' => '1',
            'emails' => ['', 'second@example.com', 'alice@example.com', ''], 'primaryEmail' => '1',
        ]);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertSame(['alice@example.com', 'second@example.com'], $existing->getEmailAddresses());
        $this->assertSame('second@example.com', $existing->getPrimaryEmail());
    }

    public function testActiveUserWithNoAddressIsRefused(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails] = $this->makeController(commissioner: true, rows: [$existing]);

        $em->expects($this->never())->method('flush');

        $request = Request::create('/admin/users/1/edit', 'POST', ['username' => 'alice', 'active' => '1', 'emails' => ['', '']]);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertSame('error', $controller->flashes[0]['type']);
        $this->assertSame(['alice@example.com'], $existing->getEmailAddresses());
    }

    public function testInactiveUserMayBeSavedWithNoAddress(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails] = $this->makeController(commissioner: true, rows: [$existing]);

        $em->expects($this->once())->method('flush');

        $request = Request::create('/admin/users/1/edit', 'POST', ['username' => 'alice', 'emails' => ['']]);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertSame([], $existing->getEmailAddresses());
        $this->assertFalse($existing->isActive());
    }

    public function testActivatingUserWithNoAddressIsRefused(): void
    {
        $existing = $this->user(1, 'alice');
        $existing->setActive(ActiveEnum::N);
        $existing->getEmails()->clear();
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails] = $this->makeController(commissioner: true, rows: [$existing]);

        $request = Request::create('/admin/users/1/edit', 'POST', ['username' => 'alice', 'active' => '1', 'emails' => ['']]);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertSame('error', $controller->flashes[0]['type']);
        $this->assertFalse($existing->isActive());
    }

    public function testDuplicateAddressInFormIsRefused(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, $teams, $teamAssignment, $em, $emails] = $this->makeController(commissioner: true, rows: [$existing]);

        $request = Request::create('/admin/users/1/edit', 'POST', [
            'username' => 'alice', 'active' => '1', 'emails' => ['a@example.com', 'A@example.com'],
        ]);
        $controller->edit(1, $request, $auth, $users, $teams, $teamAssignment, $em, $emails);

        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    // ---- send-reset ----

    public function testSendResetMailsLink(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users, , , , , $resets] = $this->makeController(commissioner: true, rows: [$existing]);

        $resets->expects($this->once())->method('sendLink')->with($existing, false)->willReturn(true);

        $response = $controller->sendReset(1, Request::create('/admin/users/1/send-reset', 'POST'), $auth, $users, $resets);

        $this->assertSame('/admin_users', $response->getTargetUrl());
        $this->assertSame('success', $controller->flashes[0]['type']);
    }

    public function testSendResetReportsUserWithoutAddress(): void
    {
        $existing = $this->user(1, 'alice');
        [$controller, $auth, $users] = $this->makeController(commissioner: true, rows: [$existing]);
        $resets = $this->createStub(PasswordResetService::class);
        $resets->method('sendLink')->willReturn(false);

        $controller->sendReset(1, Request::create('/admin/users/1/send-reset', 'POST'), $auth, $users, $resets);

        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    public function testSendResetRequiresCommissionerAndCsrf(): void
    {
        [$controller, $auth, $users, , , , , $resets] = $this->makeController(commissioner: false);
        $resets->expects($this->never())->method('sendLink');

        $response = $controller->sendReset(1, new Request(), $auth, $users, $resets);
        $this->assertSame('/', $response->getTargetUrl());

        [$controller, $auth, $users, , , , , $resets] = $this->makeController(commissioner: true, rows: [$this->user(1, 'alice')]);
        $controller->csrfValid = false;
        $this->expectException(AccessDeniedHttpException::class);
        $controller->sendReset(1, new Request(), $auth, $users, $resets);
    }

    // ---- Helpers ----

    private function user(int $id, string $username): User
    {
        $user = new User();
        $ref  = new \ReflectionProperty(User::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($user, $id);
        $user->setUsername($username);
        $user->setActive(ActiveEnum::Y);
        (new UserEmailService())->add($user, "$username@example.com");

        return $user;
    }

    private function team(int $id, string $name): Team
    {
        $team = new Team();
        $ref  = new \ReflectionProperty(Team::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($team, $id);

        return $team;
    }

    /**
     * @param User[] $rows
     * @param array<int, Team> $teamsById
     */
    private function makeController(bool $commissioner, array $rows = [], array $teamsById = []): array
    {
        $controller = new class extends AdminUserController {
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

        $byId = [];
        foreach ($rows as $row) {
            $byId[$row->getId()] = $row;
        }

        $users = $this->createStub(UserRepository::class);
        $users->method('findAllOrdered')->willReturn($rows);
        $users->method('find')->willReturnCallback(fn (int $id) => $byId[$id] ?? null);
        $users->method('findOneByUsername')->willReturnCallback(function (string $username) use ($rows) {
            foreach ($rows as $row) {
                if ($row->getUsername() === $username) {
                    return $row;
                }
            }

            return null;
        });

        $teams = $this->createStub(TeamRepository::class);
        $teams->method('getActiveTeams')->willReturn([]);

        $teamAssignment = $this->createMock(UserTeamAssignmentService::class);

        $teamEntityRepo = $this->createStub(EntityRepository::class);
        $teamEntityRepo->method('find')->willReturnCallback(fn (int $id) => $teamsById[$id] ?? null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Team::class)->willReturn($teamEntityRepo);

        $resets = $this->createMock(PasswordResetService::class);
        $resets->method('sendLink')->willReturn(true);

        return [$controller, $auth, $users, $teams, $teamAssignment, $em, new UserEmailService(), $resets];
    }
}
