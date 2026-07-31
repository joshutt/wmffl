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
            'renderer' => ['soft_break' => "<br>\n"],
        ]);
    }

    public function toHtml(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        return $this->converter->convert($this->preserveIndentation($markdown))->getContent();
    }

    /**
     * Members type real spaces/tabs to indent sub-items, but CommonMark
     * either strips leading whitespace on a soft-wrapped line or, at 4+
     * spaces, treats it as an indented code block. Replacing the leading
     * run of spaces/tabs on each line with &nbsp; sidesteps both: it's not
     * "indentation" to the parser, but still renders as non-breaking
     * spaces (matching the encoding ProposalPageParser::toMarkdown already
     * uses for imported content).
     */
    private function preserveIndentation(string $markdown): string
    {
        $lines = explode("\n", $markdown);

        foreach ($lines as &$line) {
            $line = preg_replace_callback(
                '/^[ \t]+/',
                static function (array $m): string {
                    $out = '';
                    foreach (str_split($m[0]) as $ch) {
                        $out .= $ch === "\t" ? str_repeat('&nbsp;', 4) : '&nbsp;';
                    }

                    return $out;
                },
                $line,
            );
        }

        return implode("\n", $lines);
    }
}
