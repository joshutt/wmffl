<?php

namespace App\EventSubscriber;

use App\Controller\LegacyFallbackController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LegacySessionProjector;
use App\Security\LoginAuthenticator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Keeps the legacy $_SESSION identity keys a faithful projection of the
 * Symfony security token (Phase 18 item 1):
 *
 * - login (form or remember-me) -> project, and stamp `lastlog`;
 * - logout -> clear, then go back to the page the user was on;
 * - every main request, right after the firewall -> re-project from the
 *   token, or clear when there is none. That is what carries remember-me
 *   logins, renames and deactivations through to legacy pages, and what
 *   logs out sessions left over from the pre-Symfony login once.
 *
 * Legacy URLs reach here via LegacyFallbackController's catch-all route; for
 * those it also mints the login/logout CSRF tokens base/menu.php needs while
 * the session is still open.
 */
class LegacySessionSubscriber implements EventSubscriberInterface
{
    public const LOGIN_CSRF_ATTRIBUTE = '_legacy_login_csrf';
    public const LOGOUT_CSRF_ATTRIBUTE = '_legacy_logout_csrf';

    public function __construct(
        private Security $security,
        private TokenStorageInterface $tokenStorage,
        private LegacySessionProjector $projector,
        private UserRepository $users,
        private CsrfTokenManagerInterface $csrf,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // The firewall listener runs at 8.
            KernelEvents::REQUEST => ['onRequest', 7],
            LoginSuccessEvent::class => 'onLoginSuccess',
            LogoutEvent::class => ['onLogout', -10],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->security->getFirewallConfig($request)?->isSecurityEnabled()) {
            return; // profiler/toolbar requests: leave the session alone
        }

        $this->sync($request);

        if ($request->attributes->get('_route') === LegacyFallbackController::ROUTE) {
            $request->attributes->set(self::LOGIN_CSRF_ATTRIBUTE, $this->csrf->getToken(LoginAuthenticator::CSRF_TOKEN_ID)->getValue());
            $request->attributes->set(self::LOGOUT_CSRF_ATTRIBUTE, $this->csrf->getToken('logout')->getValue());
        }
    }

    public function sync(Request $request): void
    {
        $user = $this->security->getUser(); // resolves the lazy firewall, incl. remember-me

        if ($user instanceof User) {
            if (!$user->isActive() || (string) $user->getPassword() === '') {
                // Deactivated (or password blanked) since logging in.
                $this->tokenStorage->setToken(null);
                $this->projector->clear();

                return;
            }
            if (!$this->projector->isImpersonating()) {
                $this->projector->project($user);
            }

            return;
        }

        // Anonymous: only touch the session if one already exists.
        if (($request->hasPreviousSession() || $request->hasSession() && $request->getSession()->isStarted())
            && $this->projector->hasIdentity()) {
            $this->projector->clear();
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $this->projector->project($user);
        $event->getRequest()->getSession()->set(RememberMeRenewalSubscriber::RENEWED_AT, time());

        $this->users->recordLogin($user);
    }

    public function onLogout(LogoutEvent $event): void
    {
        $this->projector->clear();

        $referer = (string) $event->getRequest()->headers->get('referer', '');
        if ($referer !== '' && self::isSameHost($event->getRequest(), $referer)) {
            $event->setResponse(new RedirectResponse($referer));
        }
    }

    private static function isSameHost(Request $request, string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return \is_string($host) && strcasecmp($host, $request->getHost()) === 0;
    }
}
