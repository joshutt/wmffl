<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Maps to the schedule table
 * Stores game matchups and scores
 */
#[ORM\Entity]
#[ORM\Table(name: 'schedule')]
class Schedule
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: Types::INTEGER)]
    private int $season;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: Types::INTEGER)]
    private int $week;

    #[ORM\Id]
    #[ORM\Column(name: 'teama', type: Types::INTEGER)]
    private int $teamA;

    #[ORM\Column(name: 'teamb', type: Types::INTEGER)]
    private int $teamB;

    #[ORM\Column(name: 'scorea', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $scoreA = null;

    #[ORM\Column(name: 'scoreb', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $scoreB = null;

    public function getSeason(): int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    public function setWeek(int $week): static
    {
        $this->week = $week;
        return $this;
    }

    public function getTeamA(): int
    {
        return $this->teamA;
    }

    public function setTeamA(int $teamA): static
    {
        $this->teamA = $teamA;
        return $this;
    }

    public function getTeamB(): int
    {
        return $this->teamB;
    }

    public function setTeamB(int $teamB): static
    {
        $this->teamB = $teamB;
        return $this;
    }

    public function getScoreA(): ?float
    {
        return $this->scoreA !== null ? (float) $this->scoreA : null;
    }

    public function setScoreA(?float $scoreA): static
    {
        $this->scoreA = $scoreA !== null ? (string) $scoreA : null;
        return $this;
    }

    public function getScoreB(): ?float
    {
        return $this->scoreB !== null ? (float) $this->scoreB : null;
    }

    public function setScoreB(?float $scoreB): static
    {
        $this->scoreB = $scoreB !== null ? (string) $scoreB : null;
        return $this;
    }
}
