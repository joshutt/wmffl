<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An ordered co-sponsor of a rule proposal. Composite PK (Issue, User);
 * SortOrder gives display order (first/primary sponsor = 0). Replaces the
 * old single-int issues.Sponsor, which could not hold co-sponsors.
 */
#[ORM\Entity]
#[ORM\Table(name: 'issue_sponsors')]
class IssueSponsor
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Issue::class, inversedBy: 'sponsors')]
    #[ORM\JoinColumn(name: 'IssueID', referencedColumnName: 'IssueID', onDelete: 'CASCADE')]
    private ?Issue $issue = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'UserID', referencedColumnName: 'UserID')]
    private ?User $user = null;

    #[ORM\Column(name: 'SortOrder', type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

    public function __construct(?Issue $issue = null, ?User $user = null, int $sortOrder = 0)
    {
        $this->issue = $issue;
        $this->user = $user;
        $this->sortOrder = $sortOrder;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function setIssue(?Issue $issue): static
    {
        $this->issue = $issue;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }
}
