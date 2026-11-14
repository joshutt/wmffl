<?php

namespace App\Tests\Controller;

use App\Controller\LegacyFallbackController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The lowest-priority catch-all that routes legacy URLs through the kernel
 * (firewall, remember-me, legacy session projection) before public/index.php
 * hands them to LegacyBridge.
 */
class LegacyFallbackControllerTest extends KernelTestCase
{
    public function testLegacyPathsMatchTheCatchAll(): void
    {
        $router = static::getContainer()->get('router');

        foreach (['/forum/comments', '/history/2001Season/week1.php', '/img/S/logo.gif', '/base/css/core.css', '/login/listsession'] as $path) {
            $this->assertSame(LegacyFallbackController::ROUTE, $router->match($path)['_route'], $path);
        }
    }

    public function testRealRoutesStillWin(): void
    {
        $router = static::getContainer()->get('router');

        $this->assertSame('home', $router->match('/')['_route']);
        $this->assertSame('player_profile', $router->match('/player/12')['_route']);
        $this->assertSame('account', $router->match('/account')['_route']);
        $this->assertSame('app_login', $router->match('/login/login')['_route']);
        $this->assertSame('password_forgot_php', $router->match('/login/forgotpassword.php')['_route']);
    }

    public function testFrontControllerRequestGetsAnEmptyHandOff(): void
    {
        $request = new Request();
        $request->attributes->set(LegacyFallbackController::LEGACY_ATTRIBUTE, true);

        $this->assertSame(204, (new LegacyFallbackController())($request)->getStatusCode());
    }

    public function testAnyOtherCallerGetsThePlain404(): void
    {
        $this->expectException(NotFoundHttpException::class);
        (new LegacyFallbackController())(Request::create('/nope'));
    }
}
