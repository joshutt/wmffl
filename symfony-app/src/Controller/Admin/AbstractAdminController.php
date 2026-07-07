<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AbstractAdminController extends AbstractController
{
    protected function requireCommissioner(AuthenticationService $auth): ?RedirectResponse
    {
        if (!$auth->isCommissioner()) {
            return new RedirectResponse('/');
        }
        return null;
    }

    /**
     * Reject a POST whose CSRF token (submitted as the `_token` field) does not
     * match the given id, throwing a 403. Call at the top of every mutating
     * admin action, after the commissioner check.
     */
    protected function assertCsrfToken(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->getPayload()->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
