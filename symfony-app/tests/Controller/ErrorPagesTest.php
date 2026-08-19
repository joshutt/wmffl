<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * First WebTestCase-based tests in this codebase (Phase 13,
 * specs/2026-08-18-error-handling/) — everything else under tests/Controller/
 * mocks render() rather than actually booting the kernel and rendering Twig.
 * That's the right default (fast, isolated), but it can't catch a bug in the
 * error templates themselves (Phase 12 found exactly that kind of bug in a
 * non-error template) — these tests exist specifically to render the new
 * branded error templates for real and assert on the HTML they produce.
 *
 * Deliberately out of scope here: LegacyBridge's own branded-404/500
 * rendering (LegacyBridgeTest.php) and the front controller's routing
 * decision (public/index.php) — both live outside the Symfony kernel's
 * request-handling boundary that WebTestCase's client operates within, so
 * they can't be exercised this way. Covered by the manual E2E checklist in
 * validation.md instead.
 */
class ErrorPagesTest extends WebTestCase
{
    public function testControllerThrown404RendersBrandedTemplate(): void
    {
        $client = $this->debugOffClient();
        $client->request('GET', '/_test/throws-404');

        $this->assertResponseStatusCodeSame(404);
        // Scoped to <main>, not a bare "h1" — base.html.twig's login modal
        // has its own <h1>Log In</h1> earlier in the DOM.
        $this->assertSelectorTextContains('main h1', 'Page not found');
    }

    public function testControllerThrown403RendersBrandedTemplate(): void
    {
        $client = $this->debugOffClient();
        $client->request('GET', '/_test/throws-403');

        $this->assertResponseStatusCodeSame(403);
        $this->assertSelectorTextContains('main h1', 'Access denied');
    }

    public function testUnroutedUrlRendersBrandedTemplate(): void
    {
        $client = $this->debugOffClient();
        $client->request('GET', '/this-route-truly-does-not-exist-anywhere');

        $this->assertResponseStatusCodeSame(404);
        $this->assertSelectorTextContains('main h1', 'Page not found');
    }

    /**
     * Symfony only renders the branded templates when kernel.debug is off —
     * otherwise it shows its own debug exception page (by design; see
     * validation.md's dev/APP_DEBUG check). Boot with debug off so these
     * tests actually exercise the templates.
     */
    private function debugOffClient(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        return static::createClient(['debug' => false]);
    }
}
