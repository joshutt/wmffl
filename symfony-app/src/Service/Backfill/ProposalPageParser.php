<?php

namespace App\Service\Backfill;

use App\Enum\IssueStatus;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Extracts rule proposals from the hand-written proposals{year}.php
 * pages. Handles both markup eras — the ~2005 `<b>Proposal…</b><br/>
 * <b>Sponsor:…</b><br/><font>Status:…</font>…<blockquote><i>rulechange`
 * shape and the 2026 Bootstrap-card shape — because both wrap the header
 * in `<b>Proposal NUM - NAME</b>`, credit sponsors as `Sponsor: …`, and
 * hold the rule change in a `<blockquote>`. HTML rule-change / rationale
 * blocks are converted to Markdown so storage is uniform.
 *
 * This is best-effort backfill tooling: what it can't cleanly extract is
 * surfaced in the report for manual review, never guessed.
 */
class ProposalPageParser
{
    private HtmlConverter $converter;

    public function __construct(?HtmlConverter $converter = null)
    {
        // hard_break=false (default) so <br> becomes a Markdown hard break
        // ("  \n") that renders as <br> — the pages rely on <br> for line
        // structure, and a soft "\n" would collapse every line into one.
        $this->converter = $converter ?? new HtmlConverter([
            'strip_tags' => true,
            'hard_break' => false,
            'suppress_errors' => true,
        ]);
    }

    /**
     * @return ParsedProposal[]
     */
    public function parse(string $pageHtml, int $season): array
    {
        $html = $this->stripPhp($pageHtml);

        // Locate each proposal header; blocks run header-to-header.
        preg_match_all(
            '/<b>\s*Proposal\s+([0-9]{4}\.[0-9A-Za-z]+)\s*[-–—]\s*(.*?)<\/b>/is',
            $html,
            $headers,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        $proposals = [];
        $count = count($headers);
        foreach ($headers as $i => $header) {
            $issueNum = trim($header[1][0]);
            $issueName = $this->plainText($header[2][0]);

            $blockStart = $header[0][1] + strlen($header[0][0]);
            $blockEnd = $i + 1 < $count ? $headers[$i + 1][0][1] : strlen($html);
            $chunk = substr($html, $blockStart, $blockEnd - $blockStart);

            $sponsors = $this->extractSponsors($chunk);
            $statusBlurb = $this->extractStatus($chunk);
            $ruleChangeHtml = $this->extractOuterBlockquote($chunk);

            $rationale = $this->extractRationale($chunk, $ruleChangeHtml, $statusBlurb);

            $proposals[] = new ParsedProposal(
                season: $season,
                issueNum: $issueNum,
                issueName: $issueName,
                sponsorNames: $sponsors,
                statusBlurb: $statusBlurb,
                status: $this->statusFromBlurb($statusBlurb),
                rationaleMarkdown: $rationale !== '' ? $rationale : null,
                ruleChangeMarkdown: $ruleChangeHtml !== null
                    ? ($this->toMarkdown($this->stripItalics($ruleChangeHtml)) ?: null)
                    : null,
            );
        }

        return $proposals;
    }

    private function stripPhp(string $html): string
    {
        return preg_replace('/<\?php.*?\?>/s', '', $html) ?? $html;
    }

    /** @return string[] */
    private function extractSponsors(string $chunk): array
    {
        if (!preg_match('/Spons[eo]r:?\s*(?:<\/?[a-z][^>]*>\s*)*([^<]+)/i', $chunk, $m)) {
            return [];
        }
        $raw = $this->plainText($m[1]);
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/\s*(?:,|&amp;|&| and )\s*/i', $raw) ?: [$raw];

        return array_values(array_filter(array_map('trim', $parts), static fn ($s) => $s !== ''));
    }

    private const STATUS_KEYWORDS = 'pass|reject|fail|withdraw|irrelevant|tabled|approv|in discussion|in progress';

    private function extractStatus(string $chunk): ?string
    {
        // Preferred: an explicit "Status: …" label (2005 and 2026 eras).
        if (preg_match('/Status:?\s*(?:<\/?[a-z][^>]*>\s*)*([^<]+)/i', $chunk, $m)) {
            $status = $this->plainText($m[1]);
            if ($status !== '') {
                return $status;
            }
        }

        // Fallback: the 2000/2001 era shows a bare coloured status blurb
        // (<font color="Red"><b>Rejected</b></font>) right after the header
        // with no label. Only accept a <font> whose text carries a decision
        // keyword, so it never grabs sponsor/rationale text.
        if (preg_match('/<font[^>]*>\s*(?:<[^>]+>\s*)*([^<]*(?:' . self::STATUS_KEYWORDS . ')[^<]*)/i', $chunk, $m)) {
            $status = $this->plainText($m[1]);
            if ($status !== '') {
                return $status;
            }
        }

        return null;
    }

    /**
     * The outermost <blockquote>…</blockquote> (balanced across nested
     * blockquotes), inner HTML only, or null if there is none.
     */
    private function extractOuterBlockquote(string $chunk): ?string
    {
        $open = stripos($chunk, '<blockquote');
        if ($open === false) {
            return null;
        }
        $openEnd = strpos($chunk, '>', $open);
        if ($openEnd === false) {
            return null;
        }

        $depth = 1;
        $pos = $openEnd + 1;
        $len = strlen($chunk);
        $innerStart = $pos;
        while ($pos < $len && $depth > 0) {
            $nextOpen = stripos($chunk, '<blockquote', $pos);
            $nextClose = stripos($chunk, '</blockquote>', $pos);
            if ($nextClose === false) {
                break;
            }
            if ($nextOpen !== false && $nextOpen < $nextClose) {
                $depth++;
                $pos = $nextOpen + 11;
            } else {
                $depth--;
                if ($depth === 0) {
                    return substr($chunk, $innerStart, $nextClose - $innerStart);
                }
                $pos = $nextClose + 13;
            }
        }

        return null;
    }

    private function extractRationale(string $chunk, ?string $ruleChangeHtml, ?string $statusBlurb): string
    {
        $fragment = $chunk;
        if ($ruleChangeHtml !== null) {
            // Remove the whole blockquote (outer tags included) so only the
            // rationale prose remains.
            $fragment = preg_replace('/<blockquote\b.*<\/blockquote>/is', '', $fragment, 1) ?? $fragment;
        }

        $markdown = $this->toMarkdown($fragment);
        if ($markdown === '') {
            return '';
        }

        $statusNorm = $statusBlurb !== null ? strtolower(trim($statusBlurb)) : null;

        // Drop the sponsor/status lines that live in the same block.
        $lines = preg_split('/\r?\n/', $markdown) ?: [];
        $kept = [];
        foreach ($lines as $line) {
            $bare = strtolower(trim(preg_replace('/[*_>#`]/', '', $line) ?? $line));
            if ($bare === '') {
                $kept[] = $line;
                continue;
            }
            if (preg_match('/^(spons[eo]r|status)\b/i', $bare)) {
                continue;
            }
            // Drop a bare status blurb line (2000-era "**Rejected**").
            if ($statusNorm !== null && $bare === $statusNorm) {
                continue;
            }
            if (
                strlen($bare) <= 70
                && preg_match('/^(' . self::STATUS_KEYWORDS . ')/i', $bare)
            ) {
                continue;
            }
            $kept[] = $line;
        }

        return trim(implode("\n", $kept));
    }

    private function statusFromBlurb(?string $blurb): IssueStatus
    {
        if ($blurb === null) {
            return IssueStatus::Open;
        }
        $lower = strtolower($blurb);
        if (str_contains($lower, 'withdraw')) {
            return IssueStatus::Withdrawn;
        }
        if (str_contains($lower, 'pass')) {
            return IssueStatus::Passed;
        }
        if (str_contains($lower, 'reject') || str_contains($lower, 'fail')) {
            return IssueStatus::Rejected;
        }

        // "In Discussion", "In Progress", "Tabled", etc. -> still Open.
        return IssueStatus::Open;
    }

    /**
     * Remove <i>/<em> tags from a rule-change block. Convention wraps the
     * whole (often multi-line, multi-blockquote) rule text in a single
     * <i>, which Markdown can't represent as emphasis spanning block
     * boundaries — it would render as stray asterisks or not italicise at
     * all. The proposals template already italicises the rule-change
     * block via `fst-italic`, so the visual intent is preserved.
     */
    private function stripItalics(string $html): string
    {
        return preg_replace('/<\/?(i|em)\b[^>]*>/i', '', $html) ?? $html;
    }

    private function toMarkdown(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        // The source pages indent nested sub-items with runs of &nbsp;.
        // html-to-markdown collapses those to ordinary spaces, and Markdown
        // then ignores leading spaces on a soft-wrapped line — so the
        // indentation is lost. Protect the non-breaking spaces behind a
        // plain-text token (control chars get stripped by the converter),
        // convert, then restore them as &nbsp; entities: CommonMark decodes
        // those back to U+00A0 on render, and unlike ordinary spaces they
        // are not collapsed, so the indentation survives to the HTML.
        $placeholder = 'XNBSPX';
        $html = str_replace(['&nbsp;', "\u{00A0}"], $placeholder, $html);

        $markdown = trim($this->converter->convert($html));

        return str_replace($placeholder, '&nbsp;', $markdown);
    }

    private function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }
}
