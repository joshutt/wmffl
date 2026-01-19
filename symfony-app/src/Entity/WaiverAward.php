<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'waiveraward')]
class WaiverAward
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: 'smallint')]
    private ?int $week = null;

    #[ORM\Id]
    #[ORM\Column(name: 'pick', type: 'smallint')]
    private ?int $pick = null;

    #[ORM\Column(name: 'teamid', type: 'smallint')]
    private ?int $teamId = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): static
    {
        $this->week = $week;
        return $this;
    }

    public function getPick(): ?int
    {
        return $this->pick;
    }

    public function setPick(int $pick): static
    {
        $this->pick = $pick;
        return $this;
    }

    public function getTeamId(): ?int
    {
        return $this->teamId;
    }

    public function setTeamId(int $teamId): static
    {
        $this->teamId = $teamId;
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
}
