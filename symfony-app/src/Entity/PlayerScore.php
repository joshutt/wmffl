<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'playerscores')]
class PlayerScore
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: 'integer')]
    private ?int $week = null;

    #[ORM\Column(name: 'pts', type: 'integer', nullable: true)]
    private ?int $pts = null;

    #[ORM\Column(name: 'active', type: 'integer', nullable: true)]
    private ?int $active = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
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

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): static
    {
        $this->week = $week;
        return $this;
    }

    public function getPts(): ?int
    {
        return $this->pts;
    }

    public function setPts(?int $pts): static
    {
        $this->pts = $pts;
        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(?int $active): static
    {
        $this->active = $active;
        return $this;
    }
}
