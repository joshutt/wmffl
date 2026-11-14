<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * The login modal's AJAX POST (base.html.twig and legacy base/menu.php).
 * Keeps the contract legacy login.php had - a plain-text body of `Ok` on
 * success or `err - <message>` on failure - so the modal JS only gained the
 * CSRF field and the "Stay logged in" checkbox.
 *
 * Hash upgrade (PasswordUpgradeBadge), remember-me, throttling and the
 * legacy $_SESSION projection all hang off Symfony's own listeners; see
 * security.yaml and LegacySessionSubscriber.
 */
class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_PATHS = ['/login/login', '/login/login.php'];
    public const CSRF_TOKEN_ID = 'authenticate';

    public function __construct(private UserRepository $users)
    {
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && \in_array($request->getPathInfo(), self::LOGIN_PATHS, true);
    }

    public function authenticate(Request $request): Passport
    {
        $payload = $request->getPayload();
        $username = trim((string) $payload->get('username', ''));
        $password = (string) $payload->get('password', '');

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge(self::CSRF_TOKEN_ID, (string) $payload->get('_csrf_token', '')),
                new RememberMeBadge(),
                new PasswordUpgradeBadge($password, $this->users),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $response = new Response('Ok', Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
        $response->headers->clearCookie('showlogin', '/', null, false, false);

        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = match (true) {
            $exception instanceof TooManyLoginAttemptsAuthenticationException => strtr(
                'Too many failed login attempts, please try again in %minutes% minute(s).',
                $exception->getMessageData()
            ),
            $exception instanceof InvalidCsrfTokenException => 'Your login form expired, please reload the page and try again.',
            default => UserChecker::MESSAGE,
        };

        return new Response('err - '.$message, Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    protected function getLoginUrl(Request $request): string
    {
        return self::LOGIN_PATHS[0];
    }

    /**
     * Not the firewall's entry point (LoginEntryPoint is) - kept consistent
     * with it in case anything calls it.
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return LoginEntryPoint::redirectToLogin();
    }
}
