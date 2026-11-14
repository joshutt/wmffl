<?php

namespace App\Tests\Template;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders base.html.twig directly to pin the navbar auth states: the login
 * state comes from the `auth` Twig global (AuthenticationService, which reads
 * the legacy $_SESSION keys LegacySessionProjector writes), not from
 * Symfony's session attribute bag.
 */
class BaseTemplateTest extends TestCase
{
    public function testLoggedInUserSeesTheirNameLinkingToTheAccountPage(): void
    {
        $html = $this->render(loggedIn: true, fullName: 'Josh Utterback', commissioner: false);

        $this->assertMatchesRegularExpression('#<a[^>]*href="/account"[^>]*>Josh Utterback</a>#', $html);
        $this->assertStringNotContainsString('data-target="#loginModal">Log In', $html);
        $this->assertStringNotContainsString('profileModal', $html);
        $this->assertStringNotContainsString('/login/newpassword', $html);
        $this->assertStringNotContainsString('href="/admin"', $html);
    }

    public function testLoggedInUserGetsOneClickLogOutWithCsrfToken(): void
    {
        $html = $this->render(loggedIn: true, fullName: 'Josh Utterback', commissioner: false);

        $this->assertMatchesRegularExpression(
            '#<form[^>]*method="post" action="/login/logout">\s*<input type="hidden" name="_csrf_token" value="test-csrf-logout">\s*<button type="submit"[^>]*>Log Out</button>#',
            $html
        );
    }

    public function testCommissionerAlsoGetsTheCommishLink(): void
    {
        $html = $this->render(loggedIn: true, fullName: 'Josh Utterback', commissioner: true);

        $this->assertStringContainsString('href="/admin"', $html);
    }

    public function testAnonymousVisitorSeesLogInButton(): void
    {
        $html = $this->render(loggedIn: false, fullName: null, commissioner: false);

        $this->assertStringContainsString('data-target="#loginModal">Log In</button>', $html);
        $this->assertStringNotContainsString('href="/account"', $html);
        $this->assertStringNotContainsString('action="/login/logout"', $html);
    }

    public function testLoginModalCarriesCsrfTokenAndStayLoggedInCheckbox(): void
    {
        $html = $this->render(loggedIn: false, fullName: null, commissioner: false);

        $this->assertStringContainsString('name="_csrf_token" value="test-csrf-authenticate"', $html);
        $this->assertMatchesRegularExpression('#<input type="checkbox"[^>]*name="_remember_me"#', $html);
        $this->assertStringContainsString('href="/login/forgotpassword"', $html);
    }

    public function testLegacyErrorPageUsesPreMintedTokens(): void
    {
        $html = $this->render(loggedIn: false, fullName: null, commissioner: false, context: [
            'legacy_login_csrf' => 'pre-minted-login',
            'legacy_logout_csrf' => 'pre-minted-logout',
        ]);

        $this->assertStringContainsString('value="pre-minted-login"', $html);
        $this->assertStringNotContainsString('test-csrf-authenticate', $html);
    }

    private function render(bool $loggedIn, ?string $fullName, bool $commissioner, array $context = []): string
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        TwigTestStubs::addCsrfToken($twig);
        $twig->addGlobal('auth', new class($loggedIn, $fullName, $commissioner) {
            public function __construct(
                private bool $loggedIn,
                private ?string $fullName,
                private bool $commissioner,
            ) {
            }

            public function isLoggedIn(): bool
            {
                return $this->loggedIn;
            }

            public function getFullName(): ?string
            {
                return $this->fullName;
            }

            public function isCommissioner(): bool
            {
                return $this->commissioner;
            }
        });

        return $twig->render('base.html.twig', $context);
    }
}
