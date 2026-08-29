<?php

namespace App\Tests\Controller;

use App\Controller\LegacyDraftResultsRedirectController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
 * Exercises the real route pattern (not hand-copied path strings) for
 * every legacy draftresults URL shape being retired in Phase 16b: both
 * filename forms (extensionless and .php), across the full 2007-2025
 * range that ever had a wrapper.
 */
class LegacyDraftResultsRedirectControllerRoutingTest extends TestCase
{
    public function testMatchesExtensionlessForm(): void
    {
        $match = $this->matcher()->match('/history/2019Season/draftresults');

        $this->assertSame('legacy_draft_results_year_redirect', $match['_route']);
        $this->assertSame('2019', $match['year']);
    }

    public function testMatchesDotPhpForm(): void
    {
        $match = $this->matcher()->match('/history/2010Season/draftresults.php');

        $this->assertSame('2010', $match['year']);
    }

    public function testMatchesEveryYearInTheFullRange(): void
    {
        $matcher = $this->matcher();

        foreach (range(2007, 2025) as $year) {
            $match = $matcher->match("/history/{$year}Season/draftresults.php");
            $this->assertSame((string) $year, $match['year']);

            $match = $matcher->match("/history/{$year}Season/draftresults");
            $this->assertSame((string) $year, $match['year']);
        }
    }

    public function testYearOutsideTheRetiredRangeDoesNotMatch(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->matcher()->match('/history/2026Season/draftresults');
    }

    public function testUnrelatedFileInTheSameYearDirectoryDoesNotMatch(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->matcher()->match('/history/2019Season/schedule.php');
    }

    public function testPostToTheLegacyFilterFormMatchesToo(): void
    {
        // The legacy filter form POSTs to itself; the route must accept
        // POST as well as GET so that submission redirects instead of
        // 405ing.
        $match = $this->matcher('POST')->match('/history/2019Season/draftresults');

        $this->assertSame('legacy_draft_results_year_redirect', $match['_route']);
    }

    private function matcher(string $method = 'GET'): UrlMatcher
    {
        $loader = new AttributeRouteControllerLoader();
        $routes = $loader->load(LegacyDraftResultsRedirectController::class);

        $context = new RequestContext();
        $context->setMethod($method);

        return new UrlMatcher($routes, $context);
    }
}
