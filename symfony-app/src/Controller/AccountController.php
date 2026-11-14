<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\UserEmailException;
use App\Security\LoginAuthenticator;
use App\Security\PasswordPolicy;
use App\Service\SecurityMailer;
use App\Service\UserEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

/**
 * Self-service account page (Phase 18 item 1) - where clicking your name in
 * the navbar goes. Change password; add, remove and choose the primary email
 * address. Every change needs the current password and sends a notice
 * (email changes go to the addresses as they were before the change).
 * Username and display name stay admin-only. Gated to ROLE_USER in
 * security.yaml.
 */
class AccountController extends AbstractController
{
    #[Route('/account', name: 'account', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'user' => $this->currentUser(),
            'minLength' => PasswordPolicy::MIN_LENGTH,
            'maxAddresses' => UserEmailService::MAX_ADDRESSES,
        ]);
    }

    #[Route('/account/password', name: 'account_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        SecurityMailer $mailer,
        Security $security,
    ): Response {
        $user = $this->currentUser();
        $this->assertCsrf($request, 'account_password');

        $current = (string) $request->request->get('currentPassword', '');
        $new = (string) $request->request->get('newPassword', '');

        if (!$hasher->isPasswordValid($user, $current)) {
            return $this->fail('Your current password was not correct');
        }
        if ($new === $current) {
            return $this->fail('The new password must be different from the current one');
        }
        if ($error = PasswordPolicy::validate($new, (string) $request->request->get('confirmPassword', ''))) {
            return $this->fail($error);
        }

        $user->setPassword($hasher->hashPassword($user, $new));
        $em->flush();
        $mailer->sendPasswordChangedNotice($user);

        // The new hash invalidates every remember-me cookie and other session
        // issued for the old one - including this session's token. Log this
        // browser straight back in (re-issuing its own cookie if it had one).
        $badges = [];
        if ($request->cookies->has('REMEMBERME')) {
            $badges[] = (new RememberMeBadge())->enable();
        }
        $security->login($user, LoginAuthenticator::class, 'main', $badges);

        $this->addFlash('success', 'Your password has been changed.');

        return $this->redirectToRoute('account');
    }

    #[Route('/account/emails', name: 'account_emails', methods: ['POST'])]
    public function updateEmails(
        Request $request,
        UserPasswordHasherInterface $hasher,
        UserEmailService $emails,
        EntityManagerInterface $em,
        SecurityMailer $mailer,
    ): Response {
        $user = $this->currentUser();
        $this->assertCsrf($request, 'account_emails');

        if (!$hasher->isPasswordValid($user, (string) $request->request->get('currentPassword', ''))) {
            return $this->fail('Your current password was not correct');
        }

        $before = $user->getEmailAddresses();
        $primaryBefore = $user->getPrimaryEmail();
        $changes = [];

        try {
            // Add first, so an address can be swapped for another in one go.
            $added = null;
            $newAddress = trim((string) $request->request->get('newEmail', ''));
            if ($newAddress !== '') {
                $added = $emails->add($user, $newAddress);
                $changes[] = 'added '.$added->getEmail();
            }

            $primary = (string) $request->request->get('primary', '');
            if ($primary === 'new' && $added !== null) {
                $emails->setPrimary($user, $added);
            } elseif ($primary !== '' && $primary !== 'new') {
                $chosen = $emails->findById($user, (int) $primary);
                if ($chosen === null) {
                    throw new UserEmailException('That address is not on this account');
                }
                $emails->setPrimary($user, $chosen);
            }

            foreach ((array) $request->request->all('remove') as $id) {
                $email = $emails->findById($user, (int) $id);
                if ($email === null) {
                    throw new UserEmailException('That address is not on this account');
                }
                $emails->remove($user, $email);
                $changes[] = 'removed '.$email->getEmail();
            }
        } catch (UserEmailException $e) {
            // Nothing was flushed; the next request reloads the user as stored.
            return $this->fail($e->getMessage());
        }

        if ($user->getPrimaryEmail() !== $primaryBefore) {
            $changes[] = 'primary address is now '.$user->getPrimaryEmail();
        }
        if ($changes === []) {
            $this->addFlash('success', 'No changes to save.');

            return $this->redirectToRoute('account');
        }

        $em->flush();
        $mailer->sendEmailChangedNotice($user, $before, implode('; ', $changes));
        $this->addFlash('success', 'Your email addresses have been updated.');

        return $this->redirectToRoute('account');
    }

    /** Old legacy change-password page. */
    #[Route('/login/newpassword', name: 'account_legacy_newpassword')]
    #[Route('/login/newpassword.php', name: 'account_legacy_newpassword_php')]
    public function legacyNewPassword(): RedirectResponse
    {
        return $this->redirect('/account', Response::HTTP_MOVED_PERMANENTLY);
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    private function fail(string $message): RedirectResponse
    {
        $this->addFlash('error', $message);

        return $this->redirectToRoute('account');
    }

    private function assertCsrf(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
