<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'positioncost')]
class PositionCost
{
    #[ORM\Id]
    #[ORM\Column(name: 'position', length: 2)]
    private ?string $position = null;

    #[ORM\Id]
    #[ORM\Column(name: 'years', type: 'integer')]
    private ?int $years = null;

    #[ORM\Id]
    #[ORM\Column(name: 'startSeason', type: 'integer')]
    private ?int $startSeason = null;

    #[ORM\Column(name: 'cost', type: 'integer')]
    private ?int $cost = 0;

    #[ORM\Column(name: 'endSeason', type: 'integer', nullable: true)]
    private ?int $endSeason = null;

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getYears(): ?int
    {
        return $this->years;
    }

    public function setYears(int $years): static
    {
        $this->years = $years;
        return $this;
    }

    public function getStartSeason(): ?int
    {
        return $this->startSeason;
    }

    public function setStartSeason(int $startSeason): static
    {
        $this->startSeason = $startSeason;
        return $this;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): static
    {
        $this->cost = $cost;
        return $this;
    }

    public function getEndSeason(): ?int
    {
        return $this->endSeason;
    }

    public function setEndSeason(?int $endSeason): static
    {
        $this->endSeason = $endSeason;
        return $this;
    }
}
