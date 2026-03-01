<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'roster')]
class Roster
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'PlayerID', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Id]
    #[ORM\Column(name: 'DateOn', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateOn = null;

    #[ORM\Column(name: 'DateOff', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateOff = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
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

    public function getDateOn(): ?\DateTime
    {
        return $this->dateOn;
    }

    public function setDateOn(\DateTime $dateOn): static
    {
        $this->dateOn = $dateOn;
        return $this;
    }

    public function getDateOff(): ?\DateTime
    {
        return $this->dateOff;
    }

    public function setDateOff(?\DateTime $dateOff): static
    {
        $this->dateOff = $dateOff;
        return $this;
    }
}
