<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nflbyes')]
class NflBye
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: 'smallint')]
    private ?int $week = null;

    #[ORM\Id]
    #[ORM\Column(name: 'nflteam', length: 3)]
    private ?string $nflTeam = null;

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

    public function getNflTeam(): ?string
    {
        return $this->nflTeam;
    }

    public function setNflTeam(string $nflTeam): static
    {
        $this->nflTeam = $nflTeam;
        return $this;
    }
}
