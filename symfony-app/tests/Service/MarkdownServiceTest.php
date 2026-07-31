<?php

namespace App\Tests\Service;

use App\Service\MarkdownService;
use PHPUnit\Framework\TestCase;

class MarkdownServiceTest extends TestCase
{
    private MarkdownService $md;

    protected function setUp(): void
    {
        $this->md = new MarkdownService();
    }

    public function testEmptyInputRendersEmptyString(): void
    {
        $this->assertSame('', $this->md->toHtml(null));
        $this->assertSame('', $this->md->toHtml('   '));
    }

    public function testBasicMarkdownRenders(): void
    {
        $html = $this->md->toHtml('Add rule **V.C.2.b** to read');
        $this->assertStringContainsString('<strong>V.C.2.b</strong>', $html);
    }

    public function testRawHtmlIsEscapedNotEmitted(): void
    {
        $html = $this->md->toHtml("Hello <script>alert('xss')</script> world");

        // The tag is neutralised (escaped), never emitted as live markup.
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testInlineHtmlDivEscaped(): void
    {
        $html = $this->md->toHtml('<div onclick="evil()">x</div>');
        $this->assertStringNotContainsString('<div', $html);
        $this->assertStringContainsString('&lt;div', $html);
    }

    public function testPlainLinkRenders(): void
    {
        $html = $this->md->toHtml('See [the rules](https://wmffl.com/rules)');
        $this->assertStringContainsString('<a href="https://wmffl.com/rules">the rules</a>', $html);
    }

    public function testUnsafeJavascriptLinkIsStripped(): void
    {
        $html = $this->md->toHtml('[click](javascript:alert(1))');
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testMultiLevelBlockquoteRendersNestedQuotesWithoutRawHtml(): void
    {
        // The 2026.3 "Blocked Kicks" shape: a quote nested in a quote,
        // with a numbered list inside.
        $markdown = <<<'MD'
        > Add rules V.B.3.e, V.B.4.e and V.B.7.f all reading:
        >
        > > Blocked Kick
        > >
        > > 1. Each one = 3 pts
        MD;

        $html = $this->md->toHtml($markdown);

        // Two levels of blockquote nested.
        $this->assertMatchesRegularExpression('/<blockquote>.*<blockquote>/s', $html);
        $this->assertStringContainsString('Blocked Kick', $html);
        $this->assertStringContainsString('<ol', $html);
        $this->assertStringContainsString('<li>Each one = 3 pts</li>', $html);
        // No raw/unescaped HTML leaked through from the source.
        $this->assertStringNotContainsString('&lt;blockquote', $html);
    }

    public function testSingleNewlineRendersAsLineBreak(): void
    {
        $html = $this->md->toHtml("First line\nSecond line");

        $this->assertStringContainsString('First line<br', $html);
        $this->assertStringContainsString('Second line', $html);
        // Not collapsed into one line with a plain space.
        $this->assertStringNotContainsString('First line Second line', $html);
    }

    public function testLeadingSpacesPreserveIndentation(): void
    {
        $html = $this->md->toHtml("Top item\n  nested item");

        $this->assertStringNotContainsString('<pre>', $html);
        $this->assertStringNotContainsString('<code>', $html);
        // &nbsp; decodes to U+00A0, rendered non-breaking (not collapsed
        // like an ordinary leading space would be).
        $this->assertStringContainsString("\u{00A0}\u{00A0}nested item", $html);
    }

    public function testLeadingTabPreservesIndentation(): void
    {
        $html = $this->md->toHtml("Top item\n\tnested item");

        $this->assertStringNotContainsString('<pre>', $html);
        $this->assertStringContainsString(str_repeat("\u{00A0}", 4) . 'nested item', $html);
    }

    public function testImportedHardBreakStyleUnaffected(): void
    {
        // Trailing double-space before the newline, matching what
        // ProposalPageParser emits for <br> runs — a Markdown hard break.
        $html = $this->md->toHtml("line one  \nline two");

        $this->assertStringContainsString('line one<br', $html);
        $this->assertStringContainsString('line two', $html);
    }
}
