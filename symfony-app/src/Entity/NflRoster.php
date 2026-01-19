<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nflrosters')]
class NflRoster
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Id]
    #[ORM\Column(name: 'nflteamid', length: 3)]
    private ?string $nflTeamId = null;

    #[ORM\Id]
    #[ORM\Column(name: 'dateon', type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateOn = null;

    #[ORM\Column(name: 'dateoff', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateOff = null;

    #[ORM\Column(name: 'pos', length: 3)]
    private ?string $pos = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getNflTeamId(): ?string
    {
        return $this->nflTeamId;
    }

    public function setNflTeamId(string $nflTeamId): static
    {
        $this->nflTeamId = $nflTeamId;
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

    public function getPos(): ?string
    {
        return $this->pos;
    }

    public function setPos(string $pos): static
    {
        $this->pos = $pos;
        return $this;
    }
}
