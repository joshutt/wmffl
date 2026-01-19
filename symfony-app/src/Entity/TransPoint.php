<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transpoints')]
class TransPoint
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'ProtectionPts', type: 'integer')]
    private ?int $protectionPts = 0;

    #[ORM\Column(name: 'TransPts', type: 'integer')]
    private ?int $transPts = 0;

    #[ORM\Column(name: 'TotalPts', type: 'integer')]
    private ?int $totalPts = 0;

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
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

    public function getProtectionPts(): ?int
    {
        return $this->protectionPts;
    }

    public function setProtectionPts(int $protectionPts): static
    {
        $this->protectionPts = $protectionPts;
        return $this;
    }

    public function getTransPts(): ?int
    {
        return $this->transPts;
    }

    public function setTransPts(int $transPts): static
    {
        $this->transPts = $transPts;
        return $this;
    }

    public function getTotalPts(): ?int
    {
        return $this->totalPts;
    }

    public function setTotalPts(int $totalPts): static
    {
        $this->totalPts = $totalPts;
        return $this;
    }
}
