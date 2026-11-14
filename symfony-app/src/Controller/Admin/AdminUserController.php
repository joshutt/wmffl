<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Exception\UserEmailException;
use App\Service\AuthenticationService;
use App\Service\PasswordResetService;
use App\Service\UserEmailService;
use App\Service\UserTeamAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Admin tool over the `user` table, its email addresses (`user_email`) and
 * team assignment (via `owners`) - Phase 14, extended in Phase 18 item 1.
 * Deliberately does not touch `commish` (site-admin flag, highest privilege
 * level, out of scope). Never shows or sets a password: new users are
 * created with a blank one (which can't log in) and emailed a set-password
 * link; "Send set-password link" does the same for anyone, e.g. after
 * reactivating them.
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
        EntityManagerInterface $em,
        UserEmailService $emails,
        PasswordResetService $resets
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $user = new User();
        // A blank password can never log in; the new user chooses theirs via
        // the set-password link emailed below.
        $user->setPassword('');

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_user');
            if ($this->applyForm($request, $user, $users, $em, $teamAssignment, $emails, null)) {
                $em->persist($user);
                $em->flush();
                $sent = $resets->sendLink($user, true);
                $this->addFlash('success', sprintf(
                    $sent ? '"%s" added and emailed a set-password link' : '"%s" added (no address, so no set-password link sent)',
                    $user->getUsername()
                ));

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
        EntityManagerInterface $em,
        UserEmailService $emails
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
            if ($this->applyForm($request, $user, $users, $em, $teamAssignment, $emails, $id)) {
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

    #[Route('/{id}/send-reset', name: 'admin_users_send_reset', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function sendReset(
        int $id,
        Request $request,
        AuthenticationService $auth,
        UserRepository $users,
        PasswordResetService $resets
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_user_send_reset');

        $user = $users->find($id);
        if (!$user) {
            throw $this->createNotFoundException("No user with id $id");
        }

        if ($resets->sendLink($user, false)) {
            $this->addFlash('success', sprintf('Set-password link sent to %s', implode(', ', $user->getEmailAddresses())));
        } else {
            $this->addFlash('error', sprintf('"%s" has no email address to send a link to', $user->getUsername()));
        }

        return $this->redirectToRoute('admin_users');
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
        UserEmailService $emails,
        ?int $excludeId
    ): bool {
        $username = trim($request->request->get('username', ''));
        $name     = trim($request->request->get('name', ''));
        $active   = $request->request->getBoolean('active');

        if ($username === '') {
            $this->addFlash('error', 'Username is required');

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

        // Validates before changing anything: 1-5 addresses for an active
        // user (so activating one with none is refused), 0-5 for inactive.
        $primaryIndex = $request->request->get('primaryEmail');
        try {
            $emails->replaceAll(
                $user,
                array_map('strval', $request->request->all('emails')),
                $primaryIndex === null || $primaryIndex === '' ? null : (int) $primaryIndex,
                $active
            );
        } catch (UserEmailException $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }

        $user->setUsername($username);
        $user->setName($name === '' ? null : $name);
        $user->setActive($active ? ActiveEnum::Y : ActiveEnum::N);
        $user->setPrimaryOwner($request->request->getBoolean('primaryOwner'));
        $teamAssignment->assign($user, $team);

        return true;
    }
}
