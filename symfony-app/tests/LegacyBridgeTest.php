<?php

namespace App\Tests;

use App\Exception\LegacyRouteNotFoundException;
use App\LegacyBridge;
use App\Service\LegacyErrorPageService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Covers the Phase 13 error-handling changes to LegacyBridge — see
 * specs/2026-08-18-error-handling/. Deliberately does not cover the
 * register_shutdown_function fatal path directly: PHP only fires shutdown
 * functions at real script termination, which isn't observable from within
 * a single PHPUnit process without ending it. That path's type-filtering
 * logic is covered by testIsFatalErrorType*() below; the end-to-end
 * behavior is covered by the manual E2E checklist in validation.md.
 */
#[AllowMockObjectsWithoutExpectations]
class LegacyBridgeTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        $this->fixtureDir = dirname(__DIR__, 2) . '/football/_test_fixtures_phase13';
        if (!is_dir($this->fixtureDir)) {
            mkdir($this->fixtureDir, 0775, true);
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->fixtureDir.'/*.php') ?: []);
        if (is_dir($this->fixtureDir)) {
            rmdir($this->fixtureDir);
        }
    }

    // ---- getLegacyScript() ----

    public function testGetLegacyScriptThrowsTypedExceptionForUnmappablePath(): void
    {
        $request = Request::create('/this-path-does-not-exist-anywhere-xyz');

        $this->expectException(LegacyRouteNotFoundException::class);
        LegacyBridge::getLegacyScript($request);
    }

    public function testGetLegacyScriptResolvesARealLegacyFile(): void
    {
        file_put_contents($this->fixtureDir.'/hello.php', '<?php // fixture');

        $path = LegacyBridge::getLegacyScript(Request::create('/_test_fixtures_phase13/hello.php'));

        $this->assertSame(realpath($this->fixtureDir.'/hello.php'), $path);
    }

    // ---- handleRequest(): unmappable path ----

    public function testHandleRequestRendersBranded404ForUnmappablePath(): void
    {
        $errorPages = $this->createMock(LegacyErrorPageService::class);
        $errorPages->expects($this->once())->method('logNotFound');
        $errorPages->expects($this->once())
            ->method('renderErrorPage')
            ->with(404)
            ->willReturn('<html>404</html>');

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(LegacyErrorPageService::class)->willReturn($errorPages);

        ob_start();
        LegacyBridge::handleRequest(
            Request::create('/this-path-does-not-exist-anywhere-xyz'),
            new Response(),
            $container,
            __DIR__,
        );
        $output = ob_get_clean();

        $this->assertSame('<html>404</html>', $output);
    }

    // ---- handleRequest(): legacy script throws ----

    public function testHandleRequestRendersBranded500WhenLegacyScriptThrows(): void
    {
        file_put_contents(
            $this->fixtureDir.'/throws.php',
            "<?php throw new \\RuntimeException('legacy boom');",
        );

        $errorPages = $this->createMock(LegacyErrorPageService::class);
        $errorPages->expects($this->once())
            ->method('logFatal')
            ->with($this->stringContains('throws.php'), $this->isInstanceOf(\RuntimeException::class));
        $errorPages->expects($this->once())
            ->method('renderErrorPage')
            ->with(500)
            ->willReturn('<html>500</html>');

        ob_start();
        LegacyBridge::handleRequest(
            Request::create('/_test_fixtures_phase13/throws.php'),
            new Response(),
            $this->mockContainer($errorPages),
            __DIR__,
        );
        $output = ob_get_clean();

        $this->assertSame('<html>500</html>', $output);
    }

    private function mockContainer(LegacyErrorPageService $errorPages): ContainerInterface
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($em);

        $seasonWeek = $this->getMockBuilder(\App\Service\SeasonWeekService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $seasonWeek->method('getCurrentSeason')->willReturn(2026);
        $seasonWeek->method('getCurrentWeek')->willReturn(1);
        $seasonWeek->method('getWeekName')->willReturn('Week 1');
        $seasonWeek->method('getPreviousWeekName')->willReturn('Week 0');
        $seasonWeek->method('getPreviousWeek')->willReturn(0);
        $seasonWeek->method('getPreviousWeekSeason')->willReturn(2026);

        $auth = $this->getMockBuilder(\App\Service\AuthenticationService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auth->method('isLoggedIn')->willReturn(false);
        $auth->method('getFullName')->willReturn(null);
        $auth->method('getTeamNumber')->willReturn(null);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            [LegacyErrorPageService::class, $errorPages],
            ['doctrine', $doctrine],
            ['App\Service\SeasonWeekService', $seasonWeek],
            ['App\Service\AuthenticationService', $auth],
        ]);

        return $container;
    }

    // ---- isFatalErrorType() ----

    public function testIsFatalErrorTypeIsFalseForNull(): void
    {
        $this->assertFalse(LegacyBridge::isFatalErrorType(null));
    }

    public function testIsFatalErrorTypeIsFalseForWarningsAndNotices(): void
    {
        $this->assertFalse(LegacyBridge::isFatalErrorType($this->error(E_WARNING)));
        $this->assertFalse(LegacyBridge::isFatalErrorType($this->error(E_NOTICE)));
        $this->assertFalse(LegacyBridge::isFatalErrorType($this->error(E_DEPRECATED)));
    }

    public function testIsFatalErrorTypeIsTrueForFatalTypes(): void
    {
        foreach ([E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR] as $type) {
            $this->assertTrue(LegacyBridge::isFatalErrorType($this->error($type)), "type $type should be fatal");
        }
    }

    /** @return array{type: int, message: string, file: string, line: int} */
    private function error(int $type): array
    {
        return ['type' => $type, 'message' => 'x', 'file' => 'x.php', 'line' => 1];
    }
}
