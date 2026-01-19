<?php

namespace App\Entity;

use App\Enum\VoteEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ballot')]
class Ballot
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Issue::class)]
    #[ORM\JoinColumn(name: 'IssueID', referencedColumnName: 'IssueID')]
    private ?Issue $issue = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Column(name: 'Result', type: 'smallint', nullable: true)]
    private ?int $result = null;

    #[ORM\Column(name: 'Vote', enumType: VoteEnum::class)]
    private ?VoteEnum $vote = VoteEnum::NoVote;

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function setIssue(?Issue $issue): static
    {
        $this->issue = $issue;
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

    public function getResult(): ?int
    {
        return $this->result;
    }

    public function setResult(?int $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getVote(): ?VoteEnum
    {
        return $this->vote;
    }

    public function setVote(VoteEnum $vote): static
    {
        $this->vote = $vote;
        return $this;
    }
}
