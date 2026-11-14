<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Lowest-priority catch-all (Phase 18 item 1). Matching every otherwise
 * unrouted URL means a legacy request now goes through the full kernel -
 * session, firewall, remember-me, LegacySessionProjector - before
 * public/index.php hands it to LegacyBridge. Without it a remembered user
 * whose first request is a legacy page would look logged out.
 *
 * The front controller marks the request with LEGACY_ATTRIBUTE; for such
 * requests this returns an empty response that index.php discards (only its
 * cookies are forwarded). Any other caller (tests, sub-requests) gets the
 * plain 404 an unrouted URL always produced, so the branded error page and
 * Phase 13 behaviour are unchanged inside the kernel.
 */
class LegacyFallbackController
{
    public const ROUTE = 'legacy_fallback';
    public const LEGACY_ATTRIBUTE = '_legacy_bridge';

    #[Route('/{path}', name: self::ROUTE, requirements: ['path' => '.*'], defaults: ['path' => ''], priority: -1000)]
    public function __invoke(Request $request): Response
    {
        if (!$request->attributes->getBoolean(self::LEGACY_ATTRIBUTE)) {
            throw new NotFoundHttpException(sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo()));
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
