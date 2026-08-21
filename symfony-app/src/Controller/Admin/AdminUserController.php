<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
use App\Service\UserTeamAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Admin tool over the `user` table and team assignment (via `owners`) -
 * legacy has no UI for either, both are edited directly in the DB today
 * (Phase 14). Deliberately does not touch `commish` (site-admin flag,
 * highest privilege level, out of scope) or password (new users self-serve
 * their first password via the existing /login/forgotpassword.php flow -
 * no password field exists anywhere in this tool).
 */
#[Route('/admin/users')]
class AdminUserController extends AbstractAdminController
{
    #[Route('', name: 'admin_users')]
    public function index(AuthenticationService $auth, UserRepository $users): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_users_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        AuthenticationService $auth,
        UserRepository $users,
        TeamRepository $teams,
        UserTeamAssignmentService $teamAssignment,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $user = new User();
        // Column is NOT NULL with no usable default; the user sets a real
        // password later via the existing /login/forgotpassword.php
        // self-service flow (Phase 14 - no password field in this tool).
        $user->setPassword('');

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_user');
            if ($this->applyForm($request, $user, $users, $em, $teamAssignment, null)) {
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', sprintf('"%s" added', $user->getUsername()));

                return $this->redirectToRoute('admin_users');
            }
        }

        return $this->render('admin/users/edit.html.twig', [
            'isEdit' => false,
            'user'   => $user,
            'teams'  => $teams->getActiveTeams(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        AuthenticationService $auth,
        UserRepository $users,
        TeamRepository $teams,
        UserTeamAssignmentService $teamAssignment,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $user = $users->find($id);
        if (!$user) {
            throw $this->createNotFoundException("No user with id $id");
        }

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_user');
            if ($this->applyForm($request, $user, $users, $em, $teamAssignment, $id)) {
                $em->flush();
                $this->addFlash('success', sprintf('"%s" updated', $user->getUsername()));

                return $this->redirectToRoute('admin_users');
            }
        }

        return $this->render('admin/users/edit.html.twig', [
            'isEdit' => true,
            'user'   => $user,
            'teams'  => $teams->getActiveTeams(),
        ]);
    }

    /**
     * Copy submitted fields onto the user, including the team-assignment
     * sync. Returns false (with an error flash) when validation fails,
     * leaving the user unchanged. $excludeId is the user's own id on
     * edit (so the uniqueness check ignores its own row), null on create.
     */
    private function applyForm(
        Request $request,
        User $user,
        UserRepository $users,
        EntityManagerInterface $em,
        UserTeamAssignmentService $teamAssignment,
        ?int $excludeId
    ): bool {
        $username = trim($request->request->get('username', ''));
        $email    = trim($request->request->get('email', ''));
        $name     = trim($request->request->get('name', ''));

        if ($username === '' || $email === '') {
            $this->addFlash('error', 'Username and email are both required');

            return false;
        }

        $existing = $users->findOneByUsername($username);
        if ($existing !== null && $existing->getId() !== $excludeId) {
            $this->addFlash('error', sprintf('Username "%s" is already taken', $username));

            return false;
        }

        $teamId = $request->request->get('teamId', '');
        $team   = null;
        if ($teamId !== '') {
            $team = $em->getRepository(Team::class)->find((int) $teamId);
            if (!$team) {
                $this->addFlash('error', 'Selected team not found');

                return false;
            }
        }

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setName($name === '' ? null : $name);
        $user->setActive($request->request->getBoolean('active') ? ActiveEnum::Y : ActiveEnum::N);
        $user->setPrimaryOwner($request->request->getBoolean('primaryOwner'));
        $teamAssignment->assign($user, $team);

        return true;
    }
}
