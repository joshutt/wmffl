<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'draftclockstop')]
class DraftClockStop
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'round', type: 'integer')]
    private ?int $round = null;

    #[ORM\Id]
    #[ORM\Column(name: 'pick', type: 'integer')]
    private ?int $pick = null;

    #[ORM\Column(name: 'timeStopped', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $timeStopped = null;

    #[ORM\Column(name: 'timeStarted', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $timeStarted = null;

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function setRound(int $round): static
    {
        $this->round = $round;
        return $this;
    }

    public function getPick(): ?int
    {
        return $this->pick;
    }

    public function setPick(int $pick): static
    {
        $this->pick = $pick;
        return $this;
    }

    public function getTimeStopped(): ?\DateTime
    {
        return $this->timeStopped;
    }

    public function setTimeStopped(?\DateTime $timeStopped): static
    {
        $this->timeStopped = $timeStopped;
        return $this;
    }

    public function getTimeStarted(): ?\DateTime
    {
        return $this->timeStarted;
    }

    public function setTimeStarted(?\DateTime $timeStarted): static
    {
        $this->timeStarted = $timeStarted;
        return $this;
    }
}
