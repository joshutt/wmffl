<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * An anonymous visitor hitting a protected URL (/admin, /account) goes to
 * the home page with the login modal open - the `showlogin` cookie the
 * modal JS in both layouts already looks for.
 */
class LoginEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return self::redirectToLogin();
    }

    public static function redirectToLogin(): RedirectResponse
    {
        $response = new RedirectResponse('/');
        $response->headers->setCookie(Cookie::create('showlogin', '1', time() + 300, '/', null, false, false));

        return $response;
    }
}
