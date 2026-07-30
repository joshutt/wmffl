<?php

namespace App\Twig;

use App\Service\MarkdownService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Exposes MarkdownService to templates as `markdown_to_html`. Use with
 * `|raw` since the filter returns already-escaped HTML:
 *   {{ issue.ruleChangeText|markdown_to_html|raw }}
 */
class MarkdownExtension extends AbstractExtension
{
    public function __construct(private MarkdownService $markdown)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown_to_html', [$this->markdown, 'toHtml'], ['is_safe' => ['html']]),
        ];
    }
}
