<?php

namespace App\Controller;

use App\Security\PasswordPolicy;
use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * "Forgot password?" (Phase 18 item 1). Replaces legacy
 * login/forgotpassword.php, which mailed a plaintext, time-seeded password
 * to whoever typed a username. Now: ask for the username, mail a single-use
 * one-hour link to all of that user's addresses, and answer identically
 * whether or not the account exists.
 */
class PasswordResetController extends AbstractController
{
    #[Route('/login/forgotpassword', name: 'password_forgot', methods: ['GET', 'POST'])]
    #[Route('/login/forgotpassword.php', name: 'password_forgot_php', methods: ['GET', 'POST'])]
    public function forgot(Request $request, PasswordResetService $resets): Response
    {
        $sent = false;
        if ($request->isMethod('POST')) {
            $this->assertCsrf($request, 'password_forgot');
            $resets->requestReset((string) $request->request->get('username', ''), $request->getClientIp());
            $sent = true;
        }

        return $this->render('login/forgot_password.html.twig', ['sent' => $sent]);
    }

    #[Route('/login/reset/{token}', name: 'password_reset', requirements: ['token' => '[A-Za-z0-9_-]+'], methods: ['GET', 'POST'])]
    public function reset(string $token, Request $request, PasswordResetService $resets): Response
    {
        $resetToken = $resets->findUsableToken($token);
        $error = null;

        if ($resetToken !== null && $request->isMethod('POST')) {
            $this->assertCsrf($request, 'password_reset');
            $new = (string) $request->request->get('newPassword', '');
            $error = PasswordPolicy::validate($new, (string) $request->request->get('confirmPassword', ''));
            if ($error === null) {
                $resets->resetPassword($resetToken, $new);

                return $this->render('login/reset_password.html.twig', ['done' => true]);
            }
        }

        $response = $this->render('login/reset_password.html.twig', [
            'done' => false,
            'valid' => $resetToken !== null,
            'username' => $resetToken?->getUser()->getUsername(),
            'error' => $error,
            'minLength' => PasswordPolicy::MIN_LENGTH,
        ], new Response(status: $resetToken !== null ? 200 : 410));
        // The token is in the URL: don't leak it to the CDNs the layout loads.
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }

    private function assertCsrf(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
