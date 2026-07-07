<?php

namespace App\Tests\EventSubscriber;

use App\Controller\Admin\AdminBecomeController;
use App\Controller\HomeController;
use App\EventSubscriber\AdminAccessSubscriber;
use App\Service\AuthenticationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdminAccessSubscriberTest extends TestCase
{
    public function testNonCommissionerIsRedirectedFromAdminController(): void
    {
        $event = $this->event([new AdminBecomeController(), 'becomeAs']);

        $this->subscriber(commissioner: false)->onController($event);

        // Controller was swapped for a redirect-to-home closure.
        $response = ($event->getController())();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testCommissionerReachesAdminControllerUnchanged(): void
    {
        $original = [new AdminBecomeController(), 'becomeAs'];
        $event = $this->event($original);

        $this->subscriber(commissioner: true)->onController($event);

        $this->assertSame($original, $event->getController());
    }

    public function testNonAdminControllerIsUntouchedForNonCommissioner(): void
    {
        $original = [new HomeController(), 'index'];
        $event = $this->event($original);

        $this->subscriber(commissioner: false)->onController($event);

        $this->assertSame($original, $event->getController());
    }

    public function testSubRequestIsIgnored(): void
    {
        $original = [new AdminBecomeController(), 'becomeAs'];
        $event = $this->event($original, HttpKernelInterface::SUB_REQUEST);

        $this->subscriber(commissioner: false)->onController($event);

        $this->assertSame($original, $event->getController());
    }

    private function subscriber(bool $commissioner): AdminAccessSubscriber
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        return new AdminAccessSubscriber($auth);
    }

    private function event(callable $controller, int $type = HttpKernelInterface::MAIN_REQUEST): ControllerEvent
    {
        return new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            $controller,
            new Request(),
            $type
        );
    }
}
