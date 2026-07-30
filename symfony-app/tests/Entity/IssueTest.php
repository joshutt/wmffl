<?php

namespace App\Tests\Entity;

use App\Entity\Issue;
use App\Entity\IssueSponsor;
use App\Entity\User;
use App\Enum\IssueStatus;
use PHPUnit\Framework\TestCase;

class IssueTest extends TestCase
{
    public function testDefaultsAreOpenAndUnpublished(): void
    {
        $issue = new Issue();
        $this->assertSame(IssueStatus::Open, $issue->getStatus());
        $this->assertFalse($issue->isPublished());
        $this->assertCount(0, $issue->getSponsors());
    }

    public function testStatusAndPublishedAreOrthogonal(): void
    {
        $issue = (new Issue())->setStatus(IssueStatus::Passed)->setPublished(true);
        $this->assertSame(IssueStatus::Passed, $issue->getStatus());
        $this->assertTrue($issue->isPublished());
    }

    public function testAddSponsorBackReferencesIssue(): void
    {
        $issue = new Issue();
        $sponsor = new IssueSponsor(null, new User(), 0);

        $issue->addSponsor($sponsor);

        $this->assertSame($issue, $sponsor->getIssue());
        $this->assertCount(1, $issue->getSponsors());
    }

    public function testAddSponsorIsIdempotent(): void
    {
        $issue = new Issue();
        $sponsor = new IssueSponsor(null, new User(), 0);

        $issue->addSponsor($sponsor);
        $issue->addSponsor($sponsor);

        $this->assertCount(1, $issue->getSponsors());
    }

    public function testRemoveSponsorDetaches(): void
    {
        $issue = new Issue();
        $sponsor = new IssueSponsor(null, new User(), 0);
        $issue->addSponsor($sponsor);

        $issue->removeSponsor($sponsor);

        $this->assertCount(0, $issue->getSponsors());
        $this->assertNull($sponsor->getIssue());
    }

    public function testSponsorCarriesSortOrder(): void
    {
        $first = new IssueSponsor(null, new User(), 0);
        $second = new IssueSponsor(null, new User(), 1);

        $this->assertSame(0, $first->getSortOrder());
        $this->assertSame(1, $second->getSortOrder());
    }

    public function testMarkdownFieldsRoundTrip(): void
    {
        $issue = (new Issue())
            ->setRationale('Because **reasons**')
            ->setRuleChangeText('> Add rule X');

        $this->assertSame('Because **reasons**', $issue->getRationale());
        $this->assertSame('> Add rule X', $issue->getRuleChangeText());
    }
}
