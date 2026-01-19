<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rankedvote')]
class RankedVote
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Issue::class)]
    #[ORM\JoinColumn(name: 'issueid', referencedColumnName: 'IssueID')]
    private ?Issue $issue = null;

    #[ORM\Id]
    #[ORM\Column(name: 'choice', length: 50)]
    private ?string $choice = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Column(name: 'rank', type: 'integer')]
    private ?int $rank = null;

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function setIssue(?Issue $issue): static
    {
        $this->issue = $issue;
        return $this;
    }

    public function getChoice(): ?string
    {
        return $this->choice;
    }

    public function setChoice(string $choice): static
    {
        $this->choice = $choice;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;
        return $this;
    }
}
