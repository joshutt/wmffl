<?php

namespace App\Entity;

use App\Enum\IssueStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A rule proposal. `Status` is the voting lifecycle (Open/Passed/
 * Rejected/Withdrawn); `Published` is the orthogonal admin gate for
 * whether members see it. `RuleChangeText` (and optionally `Rationale`)
 * is stored as Markdown and rendered with escaping. Co-sponsors live in
 * the ordered `issue_sponsors` join (the old single-int Sponsor is gone).
 */
#[ORM\Entity]
#[ORM\Table(name: 'issues')]
class Issue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'IssueID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'IssueNum', length: 10)]
    private ?string $issueNum = null;

    #[ORM\Column(name: 'IssueName', length: 120)]
    private ?string $issueName = null;

    #[ORM\Column(name: 'Description', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'Rationale', type: Types::TEXT, nullable: true)]
    private ?string $rationale = null;

    #[ORM\Column(name: 'RuleChangeText', type: Types::TEXT, nullable: true)]
    private ?string $ruleChangeText = null;

    #[ORM\Column(name: 'Season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'Deadline', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deadline = null;

    #[ORM\Column(name: 'StartDate', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(name: 'Status', type: 'string', enumType: IssueStatus::class)]
    private IssueStatus $status = IssueStatus::Open;

    #[ORM\Column(name: 'Published', type: 'boolean', options: ['default' => false])]
    private bool $published = false;

    /** @var Collection<int, IssueSponsor> */
    #[ORM\OneToMany(mappedBy: 'issue', targetEntity: IssueSponsor::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $sponsors;

    public function __construct()
    {
        $this->sponsors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssueNum(): ?string
    {
        return $this->issueNum;
    }

    public function setIssueNum(string $issueNum): static
    {
        $this->issueNum = $issueNum;
        return $this;
    }

    public function getIssueName(): ?string
    {
        return $this->issueName;
    }

    public function setIssueName(string $issueName): static
    {
        $this->issueName = $issueName;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getRationale(): ?string
    {
        return $this->rationale;
    }

    public function setRationale(?string $rationale): static
    {
        $this->rationale = $rationale;
        return $this;
    }

    public function getRuleChangeText(): ?string
    {
        return $this->ruleChangeText;
    }

    public function setRuleChangeText(?string $ruleChangeText): static
    {
        $this->ruleChangeText = $ruleChangeText;
        return $this;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTime $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getStatus(): IssueStatus
    {
        return $this->status;
    }

    public function setStatus(IssueStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;
        return $this;
    }

    /** @return Collection<int, IssueSponsor> */
    public function getSponsors(): Collection
    {
        return $this->sponsors;
    }

    public function addSponsor(IssueSponsor $sponsor): static
    {
        if (!$this->sponsors->contains($sponsor)) {
            $this->sponsors->add($sponsor);
            $sponsor->setIssue($this);
        }
        return $this;
    }

    public function removeSponsor(IssueSponsor $sponsor): static
    {
        if ($this->sponsors->removeElement($sponsor)) {
            if ($sponsor->getIssue() === $this) {
                $sponsor->setIssue(null);
            }
        }
        return $this;
    }

    public function clearSponsors(): static
    {
        $this->sponsors->clear();
        return $this;
    }
}
