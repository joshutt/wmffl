<?php

namespace App\Tests\Template;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders article/_card.html.twig directly to pin the comment-count badge:
 * always shown (including "0"), reading counts[article.id] from whatever
 * context the including template passed down.
 */
class ArticleCardTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));

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
    }

    public function testShowsCommentCountFromParentContext(): void
    {
        $article = $this->makeArticle(1);

        $html = $this->twig->render('article/_card.html.twig', [
            'article' => $article,
            'counts' => [1 => 3],
        ]);

        $this->assertStringContainsString('badge', $html);
        $this->assertStringContainsString('3', $html);
    }

    public function testShowsZeroBadgeWhenArticleHasNoActiveComments(): void
    {
        $article = $this->makeArticle(2);

        $html = $this->twig->render('article/_card.html.twig', [
            'article' => $article,
            'counts' => [1 => 3],
        ]);

        $this->assertStringContainsString('badge', $html);
        $this->assertMatchesRegularExpression('/badge[^<]*">\s*💬\s*0\s*</', $html);
    }

    public function testShowsZeroBadgeWhenCountsMissingEntirely(): void
    {
        $article = $this->makeArticle(3);

        $html = $this->twig->render('article/_card.html.twig', ['article' => $article]);

        $this->assertMatchesRegularExpression('/badge[^<]*">\s*💬\s*0\s*</', $html);
    }

    private function makeArticle(int $id): Article
    {
        $article = new Article();
        $ref = new \ReflectionProperty(Article::class, 'id');
        $ref->setValue($article, $id);
        $article->setTitle('Test Article');
        $article->setLink('img/l/abc');
        $article->setDisplayDate(new \DateTime('2026-07-01 12:00:00'));

        return $article;
    }
}
