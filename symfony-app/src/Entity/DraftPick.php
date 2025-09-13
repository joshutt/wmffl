<?php

namespace App\Entity;

use App\Repository\DraftPickRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DraftPickRepository::class)]
#[ORM\Table(name: 'draftPicks')]
#[ORM\UniqueConstraint(name: 'Season_Round_Pick_uniq', columns: ['season', 'round', 'pick'])]
#[ORM\UniqueConstraint(name: 'Season_playerid_uniq', columns: ['season', 'playerid'])]
class DraftPick
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $season = null;

    #[ORM\Column]
    private ?int $round = null;

    #[ORM\Column(nullable: true)]
    private ?int $pick = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'teamid')]
    private ?Team $team = null;

    #[ORM\Column(name: 'orgTeam', nullable: true)]
    private ?int $orgTeam = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Column(name: 'pickTime', nullable: true)]
    private ?\DateTime $pickTime = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function setRound(int $round): static
    {
        $this->round = $round;

        return $this;
    }

    public function getPick(): ?int
    {
        return $this->pick;
    }

    public function setPick(?int $pick): static
    {
        $this->pick = $pick;

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

    public function getOrgTeam(): ?int
    {
        return $this->orgTeam;
    }

    public function setOrgTeam(?int $orgTeam): static
    {
        $this->orgTeam = $orgTeam;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getPickTime(): ?\DateTime
    {
        return $this->pickTime;
    }

    public function setPickTime(?\DateTime $pickTime): static
    {
        $this->pickTime = $pickTime;

        return $this;
    }
}
