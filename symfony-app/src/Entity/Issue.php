<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(name: 'IssueName', length: 40)]
    private ?string $issueName = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'Sponsor', referencedColumnName: 'TeamID')]
    private ?Team $sponsor = null;

    #[ORM\Column(name: 'Description', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'Season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'Deadline', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deadline = null;

    #[ORM\Column(name: 'StartDate', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(name: 'Result', length: 10, nullable: true)]
    private ?string $result = null;

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

    public function getSponsor(): ?Team
    {
        return $this->sponsor;
    }

    public function setSponsor(?Team $sponsor): static
    {
        $this->sponsor = $sponsor;
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

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;
        return $this;
    }
}
