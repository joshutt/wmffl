<?php

namespace App\Entity;

use App\Enum\NflStatusEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nflstatus')]
class NflStatus
{
    #[ORM\Id]
    #[ORM\Column(name: 'nflteam', length: 3)]
    private ?string $nflTeam = null;

    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: 'integer')]
    private ?int $week = null;

    #[ORM\Column(name: 'status', nullable: true, enumType: NflStatusEnum::class)]
    private ?NflStatusEnum $status = null;

    public function getNflTeam(): ?string
    {
        return $this->nflTeam;
    }

    public function setNflTeam(string $nflTeam): static
    {
        $this->nflTeam = $nflTeam;
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

    public function getStatus(): ?NflStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?NflStatusEnum $status): static
    {
        $this->status = $status;
        return $this;
    }
}
