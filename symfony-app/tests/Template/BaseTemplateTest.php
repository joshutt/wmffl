<?php

namespace App\Tests\Template;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders base.html.twig directly to pin the navbar auth states: the login
 * state comes from the `auth` Twig global (AuthenticationService, which reads
 * the raw legacy $_SESSION keys), not from Symfony's session attribute bag.
 */
class BaseTemplateTest extends TestCase
{
    public function testLoggedInUserSeesTheirNameOpeningTheProfileModal(): void
    {
        $html = $this->render(loggedIn: true, fullName: 'Josh Utterback', commissioner: false);

        $this->assertMatchesRegularExpression('/data-target="#profileModal"[^>]*>Josh Utterback</', $html);
        $this->assertStringNotContainsString('data-target="#loginModal">Log In', $html);
        $this->assertStringContainsString('Profile Josh Utterback', $html);
        $this->assertStringNotContainsString('href="/admin"', $html);
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
        $this->assertStringNotContainsString('data-target="#profileModal"', $html);
    }

    private function render(bool $loggedIn, ?string $fullName, bool $commissioner): string
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
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

        return $twig->render('base.html.twig');
    }
}
