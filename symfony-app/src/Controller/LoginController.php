<?php

namespace App\Controller;

use App\Security\LoginEntryPoint;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Route anchors for the URLs the firewall handles (Phase 18 item 1). A POST
 * to /login/login is answered by LoginAuthenticator and /login/logout by
 * the logout listener before any controller runs; these routes exist so the
 * URLs are real Symfony routes rather than the legacy catch-all.
 */
class LoginController
{
    /** A GET (bookmark, old link) just opens the login modal on the home page. */
    #[Route('/login/login', name: 'app_login')]
    #[Route('/login/login.php', name: 'app_login_php')]
    public function login(): Response
    {
        return LoginEntryPoint::redirectToLogin();
    }

    #[Route('/login/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // Reached only when the logout listener rejected the request (e.g. a
        // GET without the CSRF token) without producing a response itself.
        return new RedirectResponse('/');
    }

    /** Old form action; 307 keeps the POST body (CSRF token) intact. */
    #[Route('/login/logout.php', name: 'app_logout_php')]
    public function logoutPhp(): Response
    {
        return new RedirectResponse('/login/logout', Response::HTTP_TEMPORARY_REDIRECT);
    }
}
