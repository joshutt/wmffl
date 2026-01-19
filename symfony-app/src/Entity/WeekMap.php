<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Maps to the weekmap table
 * Tracks the start/end dates for each week of each season
 */
#[ORM\Entity]
#[ORM\Table(name: 'weekmap')]
class WeekMap
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: Types::INTEGER)]
    private int $season;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: Types::INTEGER)]
    private int $week;

    #[ORM\Column(name: 'weekname', type: Types::STRING, length: 100)]
    private string $weekName;

    #[ORM\Column(name: 'startDate', type: Types::DATETIME_MUTABLE)]
    private \DateTime $startDate;

    #[ORM\Column(name: 'endDate', type: Types::DATETIME_MUTABLE)]
    private \DateTime $endDate;

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

    public function getWeekName(): string
    {
        return $this->weekName;
    }

    public function setWeekName(string $weekName): static
    {
        $this->weekName = $weekName;
        return $this;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }
}
