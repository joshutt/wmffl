<?php

namespace App\Tests\Template;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Renders the shared _login_required.html.twig partial, then each of the
 * ten gated pages in their logged-out state, to confirm the "must be
 * logged in" message is paired with a button opening the navbar's existing
 * #loginModal (base.html.twig) rather than leaving the visitor to hunt for
 * the separate navbar button.
 */
class LoginRequiredTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        $this->twig->addGlobal('auth', new class {
            public function isLoggedIn(): bool
            {
                return false;
            }

            public function isCommissioner(): bool
            {
                return false;
            }
        });
        $this->twig->addGlobal('app', new class {
            public function flashes(?string $type = null): array
            {
                return [];
            }
        });

        $generator = new class implements UrlGeneratorInterface {
            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return "/$name" . ($parameters ? '?' . http_build_query($parameters) : '');
            }

            public function setContext(RequestContext $context): void
            {
            }

            public function getContext(): RequestContext
            {
                return new RequestContext();
            }
        };
        $this->twig->addExtension(new RoutingExtension($generator));
        $this->twig->addFunction(new TwigFunction('csrf_token', static fn (string $id): string => "token-$id"));
    }

    public function testPartialRendersMessageAndLoginButton(): void
    {
        $html = $this->twig->render('_login_required.html.twig', ['message' => 'You must be logged in to do the thing.']);

        $this->assertStringContainsString('You must be logged in to do the thing.', $html);
        $this->assertMatchesRegularExpression('/data-target="#loginModal"[^>]*>Log In</', $html);
    }

    /**
     * @return array<string, array{0: string, 1: array<string, mixed>, 2: string}>
     */
    public static function gatedPageProvider(): array
    {
        return [
            'protections_saved not_logged_in' => [
                'transactions/protections_saved.html.twig',
                ['error' => 'not_logged_in'],
                'You must be logged in to save protections.',
            ],
            'protections' => [
                'transactions/protections.html.twig',
                ['loggedIn' => false],
                'You must be logged in to submit protections',
            ],
            'ir' => [
                'transactions/ir.html.twig',
                ['eligible' => null, 'current' => []],
                'You must be logged in to use this feature',
            ],
            'transactions list' => [
                'transactions/list.html.twig',
                ['loggedIn' => false],
                'You must be logged in to use this feature',
            ],
            'transactions confirm' => [
                'transactions/confirm.html.twig',
                ['loggedIn' => false],
                'You must be logged in to perform transactions',
            ],
            'draftdate index' => [
                'draftdate/index.html.twig',
                ['loggedIn' => false, 'season' => 2026],
                'You must be logged in to use this feature',
            ],
            'proposals ballot' => [
                'proposals/ballot.html.twig',
                ['isLoggedIn' => false],
                'You must be logged in to cast your votes.',
            ],
            'proposals submit' => [
                'proposals/submit.html.twig',
                ['isLoggedIn' => false],
                'You must be logged in to submit rule proposals.',
            ],
            'trades index' => [
                'trades/index.html.twig',
                ['loggedIn' => false],
                'You must be logged in to use this feature',
            ],
            'article publish' => [
                'article/publish.html.twig',
                ['isLoggedIn' => false],
                'You must be logged in to use this feature',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('gatedPageProvider')]
    public function testGatedPageShowsLoginButtonAlongsideItsMessage(string $template, array $context, string $expectedMessage): void
    {
        $html = $this->twig->render($template, $context);

        $this->assertStringContainsString($expectedMessage, $html);
        $this->assertMatchesRegularExpression('/data-target="#loginModal"[^>]*>Log In</', $html);
    }
}
