<?php

namespace App\Service;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\ConverterInterface;

/**
 * Renders member-authored Markdown (proposal rationale / rule-change
 * text) to HTML. Members are not admins, so raw HTML in the input is
 * escaped rather than passed through, and unsafe link schemes
 * (javascript:, data:, etc.) are stripped.
 */
class MarkdownService
{
    private ConverterInterface $converter;

    public function __construct(?ConverterInterface $converter = null)
    {
        $this->converter = $converter ?? new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    public function toHtml(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        return $this->converter->convert($markdown)->getContent();
    }
}
