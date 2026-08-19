<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * Renders the branded error templates and logs the underlying failure, for
 * use from LegacyBridge — which runs outside Symfony's normal
 * controller/exception-handling flow (the kernel has already finished
 * handling the request by the time LegacyBridge runs), so it can't rely on
 * Symfony's ErrorController or exception listener to do this automatically.
 * See specs/2026-08-18-error-handling/ (Phase 13).
 */
class LegacyErrorPageService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function renderErrorPage(int $statusCode): string
    {
        $template = match ($statusCode) {
            404 => 'bundles/TwigBundle/Exception/error404.html.twig',
            403 => 'bundles/TwigBundle/Exception/error403.html.twig',
            default => 'bundles/TwigBundle/Exception/error.html.twig',
        };

        return $this->twig->render($template);
    }

    /**
     * A legacy path that couldn't be routed or mapped to a file at all —
     * expected traffic (bad links, bots), logged at warning so it doesn't
     * compete with real errors.
     */
    public function logNotFound(string $message): void
    {
        $this->logger->warning($message);
    }

    /**
     * A legacy script that crashed — a real failure, logged at error with
     * the original exception (if any) attached for a stack trace.
     */
    public function logFatal(string $message, ?\Throwable $exception = null): void
    {
        $this->logger->error($message, $exception !== null ? ['exception' => $exception] : []);
    }
}
