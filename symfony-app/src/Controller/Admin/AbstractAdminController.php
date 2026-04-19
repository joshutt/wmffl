<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractAdminController extends AbstractController
{
    protected function requireCommissioner(AuthenticationService $auth): ?RedirectResponse
    {
        if (!$auth->isCommissioner()) {
            return new RedirectResponse('/');
        }
        return null;
    }
}
