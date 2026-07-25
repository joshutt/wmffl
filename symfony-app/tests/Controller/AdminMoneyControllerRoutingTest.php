<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminMoneyController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Regression coverage for the /admin/money/updatePaid routing.
 *
 * In production, money.js posted to a *relative* "recordChange" URL. On a
 * page served at /admin/money/updatePaid/{season} (an explicit season in
 * the path), the browser resolved that relative to
 * /admin/money/updatePaid/recordChange instead of /admin/money/recordChange
 * - which matched this same route with $season bound to the literal string
 * "recordChange", throwing a TypeError against the ?int parameter. These
 * tests exercise the real route attributes (not hand-copied path strings)
 * for both the no-season and explicit-season page URLs, and lock in the
 * `\d+` requirement that makes the broken URL 404 instead of crash.
 */
class AdminMoneyControllerRoutingTest extends TestCase
{
    public function testUpdatePaidMatchesWithoutSeason(): void
    {
        $match = $this->matcher()->match('/admin/money/updatePaid');

        $this->assertSame('admin_money_paid', $match['_route']);
        $this->assertNull($match['season']);
    }

    public function testUpdatePaidMatchesWithExplicitSeason(): void
    {
        $match = $this->matcher()->match('/admin/money/updatePaid/2025');

        $this->assertSame('admin_money_paid', $match['_route']);
        $this->assertSame('2025', $match['season']);
    }

    public function testUpdatePaidRejectsNonNumericSeasonSegment(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        // This is the exact URL the buggy relative AJAX call produced.
        $this->matcher()->match('/admin/money/updatePaid/recordChange');
    }

    public function testRecordChangeIsReachableAtItsAbsolutePath(): void
    {
        $context = new RequestContext(method: 'POST');
        $matcher = new UrlMatcher($this->routes(), $context);
        $match = $matcher->match('/admin/money/recordChange');

        $this->assertSame('admin_money_record_change', $match['_route']);
    }

    private function routes(): RouteCollection
    {
        $loader = new AttributeRouteControllerLoader();

        return $loader->load(AdminMoneyController::class);
    }

    private function matcher(): UrlMatcher
    {
        return new UrlMatcher($this->routes(), new RequestContext());
    }
}
