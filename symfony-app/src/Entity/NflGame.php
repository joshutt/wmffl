<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nflgames')]
class NflGame
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: 'integer')]
    private ?int $week = null;

    #[ORM\Id]
    #[ORM\Column(name: 'homeTeam', length: 3)]
    private ?string $homeTeam = null;

    #[ORM\Id]
    #[ORM\Column(name: 'roadTeam', length: 3)]
    private ?string $roadTeam = null;

    #[ORM\Column(name: 'kickoff', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $kickoff = null;

    #[ORM\Column(name: 'secRemain', type: 'integer')]
    private ?int $secRemain = 0;

    #[ORM\Column(name: 'complete', type: 'integer')]
    private ?int $complete = 0;

    #[ORM\Column(name: 'homeScore', type: 'integer', nullable: true)]
    private ?int $homeScore = null;

    #[ORM\Column(name: 'roadScore', type: 'integer', nullable: true)]
    private ?int $roadScore = null;

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

    public function getHomeTeam(): ?string
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(string $homeTeam): static
    {
        $this->homeTeam = $homeTeam;
        return $this;
    }

    public function getRoadTeam(): ?string
    {
        return $this->roadTeam;
    }

    public function setRoadTeam(string $roadTeam): static
    {
        $this->roadTeam = $roadTeam;
        return $this;
    }

    public function getKickoff(): ?\DateTime
    {
        return $this->kickoff;
    }

    public function setKickoff(\DateTime $kickoff): static
    {
        $this->kickoff = $kickoff;
        return $this;
    }

    public function getSecRemain(): ?int
    {
        return $this->secRemain;
    }

    public function setSecRemain(int $secRemain): static
    {
        $this->secRemain = $secRemain;
        return $this;
    }

    public function getComplete(): ?int
    {
        return $this->complete;
    }

    public function setComplete(int $complete): static
    {
        $this->complete = $complete;
        return $this;
    }

    public function getHomeScore(): ?int
    {
        return $this->homeScore;
    }

    public function setHomeScore(?int $homeScore): static
    {
        $this->homeScore = $homeScore;
        return $this;
    }

    public function getRoadScore(): ?int
    {
        return $this->roadScore;
    }

    public function setRoadScore(?int $roadScore): static
    {
        $this->roadScore = $roadScore;
        return $this;
    }
}
