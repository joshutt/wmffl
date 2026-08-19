<?php

namespace App\Exception;

/**
 * Thrown by LegacyBridge::getLegacyScript() when a request has no matching
 * Symfony route AND doesn't map to a real file under /football/ either —
 * a genuine dead end, distinct from every other failure mode in the legacy
 * mapping logic. Caught by LegacyBridge::handleRequest(), which renders the
 * branded 404 page instead of letting this escape as an uncaught exception.
 */
class LegacyRouteNotFoundException extends \RuntimeException
{
}
