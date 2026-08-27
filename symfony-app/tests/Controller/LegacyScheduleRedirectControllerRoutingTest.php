<?php

namespace App\Tests\Controller;

use App\Controller\LegacyScheduleRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Exercises the real route pattern (not hand-copied path strings) for
 * every legacy schedule URL shape being retired in Phase 16a: the
 * 2000-2025 DB-backed wrapper's extensionless and .php forms, and the
 * 1992-1999 hand-written NNschedule.php pages.
 */
class LegacyScheduleRedirectControllerRoutingTest extends TestCase
{
    public function testMatches2000sExtensionlessForm(): void
    {
        $match = $this->matcher()->match('/history/2016Season/schedule');

        $this->assertSame('legacy_schedule_year_redirect', $match['_route']);
        $this->assertSame('2016', $match['year']);
    }

    public function testMatches2000sDotPhpForm(): void
    {
        $match = $this->matcher()->match('/history/2005Season/schedule.php');

        $this->assertSame('2005', $match['year']);
    }

    public function testMatches1990sNNScheduleForm(): void
    {
        $match = $this->matcher()->match('/history/1992Season/92schedule.php');

        $this->assertSame('1992', $match['year']);
    }

    public function testMatchesEveryYearInTheFullRange(): void
    {
        $matcher = $this->matcher();

        foreach (range(1992, 1999) as $year) {
            $yy = substr((string) $year, 2, 2);
            $match = $matcher->match("/history/{$year}Season/{$yy}schedule.php");
            $this->assertSame((string) $year, $match['year']);
        }
        foreach (range(2000, 2025) as $year) {
            $match = $matcher->match("/history/{$year}Season/schedule.php");
            $this->assertSame((string) $year, $match['year']);
        }
    }

    public function testYearOutsideTheRetiredRangeDoesNotMatch(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->matcher()->match('/history/2026Season/schedule');
    }

    public function testUnrelatedFileInTheSameYearDirectoryDoesNotMatch(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->matcher()->match('/history/2016Season/weekstandings.php');
    }

    private function matcher(): UrlMatcher
    {
        $loader = new AttributeRouteControllerLoader();
        $routes = $loader->load(LegacyScheduleRedirectController::class);

        return new UrlMatcher($routes, new RequestContext());
    }
}
