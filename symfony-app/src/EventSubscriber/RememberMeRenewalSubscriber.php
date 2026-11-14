<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;

/**
 * "Stay logged in" is a sliding 30 days: the clock restarts on every visit.
 * Symfony re-issues the cookie whenever it logs someone back in from it, but
 * not while their PHP session is still alive - so a user with a long-lived
 * session would see the cookie age out. This re-issues it at most once a
 * day for a logged-in user who has the cookie (i.e. opted in).
 */
class RememberMeRenewalSubscriber implements EventSubscriberInterface
{
    public const RENEWED_AT = '_remember_me_renewed_at';
    public const COOKIE_NAME = 'REMEMBERME';
    public const RENEW_EVERY = 86400;

    public function __construct(
        private Security $security,
        #[Autowire(service: 'security.authenticator.remember_me_handler.main')]
        private RememberMeHandlerInterface $rememberMe,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // After LegacySessionSubscriber (7), which may have dropped the token.
        return [KernelEvents::REQUEST => ['onRequest', 6]];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !$request->cookies->has(self::COOKIE_NAME)) {
            return;
        }
        if (!$this->security->getFirewallConfig($request)?->isSecurityEnabled()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || !$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        $renewedAt = (int) $session->get(self::RENEWED_AT, 0);
        if (time() - $renewedAt < self::RENEW_EVERY) {
            return;
        }

        $this->rememberMe->createRememberMeCookie($user);
        $session->set(self::RENEWED_AT, time());
    }
}
