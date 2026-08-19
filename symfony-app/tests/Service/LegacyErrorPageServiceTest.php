<?php

namespace App\Tests\Service;

use App\Service\LegacyErrorPageService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class LegacyErrorPageServiceTest extends TestCase
{
    // ---- renderErrorPage ----

    public function testRenderErrorPageUses404TemplateFor404(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with('bundles/TwigBundle/Exception/error404.html.twig')
            ->willReturn('<html>404</html>');

        $service = new LegacyErrorPageService($twig, $this->createMock(LoggerInterface::class));

        $this->assertSame('<html>404</html>', $service->renderErrorPage(404));
    }

    public function testRenderErrorPageUses403TemplateFor403(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with('bundles/TwigBundle/Exception/error403.html.twig')
            ->willReturn('<html>403</html>');

        $service = new LegacyErrorPageService($twig, $this->createMock(LoggerInterface::class));

        $this->assertSame('<html>403</html>', $service->renderErrorPage(403));
    }

    public function testRenderErrorPageFallsBackToGenericTemplateForOtherCodes(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with('bundles/TwigBundle/Exception/error.html.twig')
            ->willReturn('<html>500</html>');

        $service = new LegacyErrorPageService($twig, $this->createMock(LoggerInterface::class));

        $this->assertSame('<html>500</html>', $service->renderErrorPage(500));
    }

    // ---- logNotFound / logFatal ----

    public function testLogNotFoundLogsAtWarning(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with('no route for /foo');

        $service = new LegacyErrorPageService($this->createMock(Environment::class), $logger);
        $service->logNotFound('no route for /foo');
    }

    public function testLogFatalLogsAtErrorWithoutException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('boom', []);

        $service = new LegacyErrorPageService($this->createMock(Environment::class), $logger);
        $service->logFatal('boom');
    }

    public function testLogFatalAttachesExceptionContext(): void
    {
        $exception = new \RuntimeException('legacy fatal');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('boom', ['exception' => $exception]);

        $service = new LegacyErrorPageService($this->createMock(Environment::class), $logger);
        $service->logFatal('boom', $exception);
    }
}
