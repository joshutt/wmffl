<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'playerteams')]
class PlayerTeam
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Id]
    #[ORM\Column(name: 'nflteam', length: 3)]
    private ?string $nflTeam = null;

    #[ORM\Id]
    #[ORM\Column(name: 'startdate', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(name: 'enddate', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $endDate = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getNflTeam(): ?string
    {
        return $this->nflTeam;
    }

    public function setNflTeam(string $nflTeam): static
    {
        $this->nflTeam = $nflTeam;
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

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }
}
