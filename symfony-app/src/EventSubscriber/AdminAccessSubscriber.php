<?php

namespace App\EventSubscriber;

use App\Service\AuthenticationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Central guard for the /admin area: every controller under
 * App\Controller\Admin requires a commissioner. Enforcing it here in one place
 * means a newly added admin controller can't silently ship without the check.
 *
 * The per-action requireCommissioner() calls stay in place as the layer that
 * defines each endpoint's exact response (and are what the controller unit
 * tests exercise, since those call the actions directly); this subscriber is
 * the safety net for real HTTP requests. It mirrors requireCommissioner() by
 * redirecting non-commissioners to '/'.
 */
class AdminAccessSubscriber implements EventSubscriberInterface
{
    public function __construct(private AuthenticationService $auth)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onController'];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();
        $class = match (true) {
            is_array($controller) => $controller[0]::class,
            is_object($controller) => $controller::class,
            default => '',
        };

        if (!str_starts_with($class, 'App\\Controller\\Admin\\')) {
            return;
        }

        if (!$this->auth->isCommissioner()) {
            $event->setController(static fn (): RedirectResponse => new RedirectResponse('/'));
        }
    }
}
