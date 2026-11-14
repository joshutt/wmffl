<?php

namespace App\Tests\Template;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Stand-ins for framework Twig functions the bare test Environments don't
 * have. base.html.twig's login/logout forms call csrf_token() (Phase 18
 * item 1), so every test rendering a page on that layout needs it.
 */
final class TwigTestStubs
{
    public static function addCsrfToken(Environment $twig): void
    {
        $twig->addFunction(new TwigFunction('csrf_token', static fn (string $id): string => 'test-csrf-'.$id));
    }
}
