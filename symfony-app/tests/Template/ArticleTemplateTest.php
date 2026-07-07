<?php

namespace App\Tests\Template;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Extension\HtmlSanitizerExtension;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders article/_article.html.twig directly to pin the Edited-line rule:
 * shown only when lastEdited is non-null and falls on a different calendar
 * day than displayDate.
 */
class ArticleTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));

        // Mirror config/packages/html_sanitizer.yaml so the sanitize_html
        // filter used by _article.html.twig resolves in the bare test env.
        $sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowStaticElements()
                ->allowRelativeLinks()
                ->allowRelativeMedias()
        );
        $sanitizers = new class($sanitizer) implements ContainerInterface {
            public function __construct(private HtmlSanitizer $sanitizer)
            {
            }

            public function get(string $id): HtmlSanitizer
            {
                return $this->sanitizer;
            }

            public function has(string $id): bool
            {
                return true;
            }
        };
        $this->twig->addExtension(new HtmlSanitizerExtension($sanitizers));
    }

    public function testNeverEditedArticleShowsNoEditedLine(): void
    {
        $html = $this->renderArticle(lastEdited: null);

        $this->assertStringContainsString('Published: Jul 01, 2026', $html);
        $this->assertStringNotContainsString('Edited:', $html);
    }

    public function testSameDayEditShowsNoEditedLine(): void
    {
        $html = $this->renderArticle(lastEdited: new \DateTime('2026-07-01 18:00:00'));

        $this->assertStringNotContainsString('Edited:', $html);
    }

    public function testLaterDayEditShowsEditedLine(): void
    {
        $html = $this->renderArticle(lastEdited: new \DateTime('2026-07-03 09:00:00'));

        $this->assertStringContainsString('Published: Jul 01, 2026', $html);
        $this->assertStringContainsString('Edited: Jul 03, 2026', $html);
    }

    private function renderArticle(?\DateTime $lastEdited): string
    {
        $article = new Article();
        $article->setTitle('Test Article');
        $article->setLink('img/l/abc');
        $article->setCaption('Caption');
        $article->setText('<p>Body</p>');
        $article->setDisplayDate(new \DateTime('2026-07-01 12:00:00'));
        $article->setLastEdited($lastEdited);

        return $this->twig->render('article/_article.html.twig', ['article' => $article]);
    }
}
