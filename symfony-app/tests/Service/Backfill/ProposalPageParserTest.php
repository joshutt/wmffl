<?php

namespace App\Tests\Service\Backfill;

use App\Enum\IssueStatus;
use App\Service\Backfill\ProposalPageParser;
use App\Service\MarkdownService;
use PHPUnit\Framework\TestCase;

class ProposalPageParserTest extends TestCase
{
    private ProposalPageParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ProposalPageParser();
    }

    // ---- 2005 (<p><b>…) era ----

    private const ERA_2005 = <<<'HTML'
    <?php include 'base/menu.php'; ?>
    <h1>Current Rule Proposals</h1>
    <p><b>Proposal 2004.2a - 0-0 Tie Breaker</b><br/>
    <b>Sponsor: MeggaMen</b><br/>
    <font color="red">Status: Passed (6-3, 1 Abstain)</font><br/>
    In the event that two teams playing each other both have negative scores.<br/>
    <blockquote><i>Add rule V.C.2.b to read the following:
    <blockquote>If the score of both teams equals 0, the team who scored the most combined points will recieve a 1 point bonus and win the game 1-0.</blockquote>
    </i></blockquote></p>

    <p><b>Proposal 2005.1 - Replacement Coach</b><br/>
    <b>Sponsor: Gallic Warriors</b><br/>
    Teams may select a replacement coach for their coach's bye week.<br/>
    <blockquote><i>Add rule V.A.2.a reading the following:
    <blockquote>Once a season a team may activate a HC not on their roster.</blockquote>
    </i></blockquote></p>

    <p><b>Proposal 2005.2 - Define a player</b><br/>
    Due to a conflict clarify the definition of a player.<br/>
    <blockquote><i>Add rule IV.A.1 reading:
    <blockquote>A player is any person currently, or previously on an NFL roster</blockquote>
    </i></blockquote></p>
    <?php include '/base/footer.php' ?>
    HTML;

    public function testParsesAll2005Proposals(): void
    {
        $proposals = $this->parser->parse(self::ERA_2005, 2005);

        $this->assertCount(3, $proposals);
        $this->assertSame('2004.2a', $proposals[0]->issueNum);
        $this->assertSame('0-0 Tie Breaker', $proposals[0]->issueName);
        $this->assertSame('2005.1', $proposals[1]->issueNum);
        $this->assertSame('2005.2', $proposals[2]->issueNum);
    }

    public function testExtracts2005SponsorAndStatus(): void
    {
        $proposals = $this->parser->parse(self::ERA_2005, 2005);

        $this->assertSame(['MeggaMen'], $proposals[0]->sponsorNames);
        $this->assertSame(IssueStatus::Passed, $proposals[0]->status);
        $this->assertStringContainsString('Passed', $proposals[0]->statusBlurb);
    }

    public function testProposalWithNoSponsorHasEmptySponsorList(): void
    {
        // 2005.2 has no "Sponsor:" line.
        $proposals = $this->parser->parse(self::ERA_2005, 2005);

        $this->assertSame([], $proposals[2]->sponsorNames);
    }

    public function testRuleChangeConvertedToMarkdownWithNestedQuote(): void
    {
        $proposals = $this->parser->parse(self::ERA_2005, 2005);

        $md = $proposals[0]->ruleChangeMarkdown;
        $this->assertNotNull($md);
        $this->assertStringContainsString('Add rule V.C.2.b', $md);
        // The nested blockquote becomes a nested markdown quote.
        $this->assertStringContainsString('>', $md);
        $this->assertStringContainsString('1 point bonus', $md);
        // No raw HTML tags survive.
        $this->assertStringNotContainsString('<blockquote', $md);
    }

    public function testRationaleExcludesSponsorStatusAndRuleChange(): void
    {
        $proposals = $this->parser->parse(self::ERA_2005, 2005);

        $rationale = $proposals[0]->rationaleMarkdown;
        $this->assertNotNull($rationale);
        $this->assertStringContainsString('negative scores', $rationale);
        $this->assertStringNotContainsString('Sponsor', $rationale);
        $this->assertStringNotContainsString('Status', $rationale);
        $this->assertStringNotContainsString('V.C.2.b', $rationale);
    }

    // ---- 2026 (Bootstrap card) era ----

    private const ERA_2026 = <<<'HTML'
    <?php $title = 'WMFFL Rule Proposals'; include 'base/menu.php'; ?>
    <div class="container">
        <div class="card m-3 p-2 bg-light">
            <b>Proposal 2026.1 - Reduce Roster Size</b>
            <b>Sponsor: Richard Lawson</b><br/>
            <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
            <p>The draft to go from 16 rounds to 14 rounds.</p>
            <blockquote class="mb-0 mt-2 px-3"><i>Change text in rule VI.D to be "The draft will be 14 rounds"</i></blockquote>
        </div>
        <div class="card m-3 p-2 bg-light">
            <b>Proposal 2026.3 - Blocked Kicks</b>
            <b>Sponsor: Josh Utterback</b><br/>
            <span class="ballot">Status: <span class="status">In Discussion</span></span><br/>
            <p>In 2006 we passed a rule giving players 3 points for every blocked kick.</p>
            <blockquote class="mb-0 mt-2 px-3"><i>Add rules V.B.3.e all reading:
                <blockquote><i>Blocked Kick<br/>i. Each one = 3 pts</i></blockquote>
            </i></blockquote>
        </div>
    </div>
    <?php include 'base/footer.php'; ?>
    HTML;

    public function testParses2026CardEra(): void
    {
        $proposals = $this->parser->parse(self::ERA_2026, 2026);

        $this->assertCount(2, $proposals);
        $this->assertSame('2026.1', $proposals[0]->issueNum);
        $this->assertSame('Reduce Roster Size', $proposals[0]->issueName);
        $this->assertSame(['Richard Lawson'], $proposals[0]->sponsorNames);
        // "In Discussion" is not a decided outcome -> Open.
        $this->assertSame(IssueStatus::Open, $proposals[0]->status);
    }

    public function testParses2026NestedBlockedKicks(): void
    {
        $proposals = $this->parser->parse(self::ERA_2026, 2026);

        $md = $proposals[1]->ruleChangeMarkdown;
        $this->assertNotNull($md);
        $this->assertStringContainsString('Blocked Kick', $md);
        $this->assertStringContainsString('Each one = 3 pts', $md);
        $this->assertStringNotContainsString('<blockquote', $md);
    }

    public function testTeamNameSponsorsAreCarriedThroughRaw(): void
    {
        // "Gallic Warriors" is a team name; the parser keeps it verbatim
        // (resolution to an owner happens later).
        $proposals = $this->parser->parse(self::ERA_2005, 2005);
        $this->assertSame(['Gallic Warriors'], $proposals[1]->sponsorNames);
    }

    // ---- 2000 (bare coloured status, no "Status:" label) era ----

    private const ERA_2000 = <<<'HTML'
    <?php include "base/menu.php"; ?>
    <P><B>Proposal 2000.1 - Change Name of the League</B><BR>
    <FONT COLOR="Red"><B>Rejected</B></FONT><BR>
    This would cause the league to be renamed. Other suggestions are open.</P>

    <P><B>Proposal 2000.4 - Transaction Rules Change</B><BR>
    <FONT COLOR="Red"><B>Passed</B></FONT><BR>
    Change the transaction rules so a pickup counts as a point.</P>
    HTML;

    public function testBareColouredStatusIsDetectedAndKeptOutOfRationale(): void
    {
        $proposals = $this->parser->parse(self::ERA_2000, 2000);

        $this->assertCount(2, $proposals);
        $this->assertSame(IssueStatus::Rejected, $proposals[0]->status);
        $this->assertSame(IssueStatus::Passed, $proposals[1]->status);

        // The status word must not leak into the rationale prose.
        $this->assertNotNull($proposals[0]->rationaleMarkdown);
        $this->assertStringContainsString('renamed', $proposals[0]->rationaleMarkdown);
        $this->assertStringNotContainsStringIgnoringCase('Rejected', $proposals[0]->rationaleMarkdown);
    }

    // ---- rule-change rendering fidelity (regression) ----

    /**
     * The 2025.1 shape: a single <i> wraps a multi-line, nested-blockquote
     * rule change. Emphasis can't span block boundaries in Markdown, and
     * the page's <br/> line structure must survive. The converted Markdown
     * must therefore (a) render each <br/> as a line break and (b) NOT
     * leave stray literal asterisks from block-spanning <i>.
     */
    private const RULE_CHANGE_WRAPPED_ITALIC = <<<'HTML'
    <div class="card">
        <b>Proposal 2025.1 - Reduce Kicker Points</b>
        <b>Sponsor: Someone</b><br/>
        <p>Reducing kicker points.</p>
        <blockquote class="mb-0"><i>Change text in rule V.B.5.b to read:<br/>
            <blockquote>
                b. Field Goal <br/>
                &nbsp;&nbsp;i. Made 0-39 yards = 1 pt<br/>
                &nbsp;&nbsp;ii. Made 40-49 yards = 2 pts
            </blockquote>
        </i></blockquote>
    </div>
    HTML;

    public function testRuleChangeKeepsLineBreaksAndDropsBlockSpanningItalics(): void
    {
        $proposals = $this->parser->parse(self::RULE_CHANGE_WRAPPED_ITALIC, 2025);
        $md = $proposals[0]->ruleChangeMarkdown;
        $this->assertNotNull($md);

        // Rendered through the real CommonMark service (as the template does).
        $html = (new MarkdownService())->toHtml($md);

        // Each source <br/> becomes a rendered line break — lines don't merge.
        $this->assertGreaterThanOrEqual(2, substr_count($html, '<br'));
        $this->assertStringContainsString('Made 0-39 yards = 1 pt', $html);
        $this->assertStringContainsString('Made 40-49 yards = 2 pts', $html);

        // No stray literal asterisk from a block-spanning <i>, and the
        // italics tag itself is gone (the template italicises the block).
        $this->assertStringNotContainsString('*', $md);
        $this->assertStringNotContainsString('<i>', $html);
    }

    /**
     * The source pages indent nested sub-items with runs of &nbsp;. That
     * indentation must survive to the rendered HTML — ordinary leading
     * spaces would be collapsed, so the sub-items must carry non-breaking
     * spaces (U+00A0) once rendered.
     */
    private const RULE_CHANGE_INDENTED = <<<'HTML'
    <div class="card">
        <b>Proposal 2025.1 - Reduce Kicker Points</b>
        <b>Sponsor: Someone</b><br/>
        <blockquote class="mb-0"><i>Change text in rule V.B.5.b to read:<br/>
            <blockquote>
                b. Field Goal <br/>
                &nbsp;&nbsp;&nbsp;&nbsp;i. Made 0-39 yards = 1 pt<br/>
                &nbsp;&nbsp;&nbsp;&nbsp;ii. Made 40-49 yards = 2 pts
            </blockquote>
        </i></blockquote>
    </div>
    HTML;

    public function testNestedIndentationSurvivesAsNonBreakingSpaces(): void
    {
        $proposals = $this->parser->parse(self::RULE_CHANGE_INDENTED, 2025);
        $md = $proposals[0]->ruleChangeMarkdown;
        $this->assertNotNull($md);

        // The indentation is stored as &nbsp; entities, not bare spaces.
        $this->assertStringContainsString('&nbsp;i. Made 0-39', $md);

        // Rendered through the real CommonMark service, the sub-items are
        // indented with non-breaking spaces (U+00A0), not collapsed.
        $html = (new MarkdownService())->toHtml($md);
        $this->assertStringContainsString("\u{00A0}i. Made 0-39 yards = 1 pt", $html);
        $this->assertStringContainsString("\u{00A0}ii. Made 40-49 yards = 2 pts", $html);
    }

    public function testEmptyPageYieldsNothing(): void
    {
        $this->assertSame([], $this->parser->parse('<p>no proposals here</p>', 2000));
    }
}
