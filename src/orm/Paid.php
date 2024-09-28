<?php

namespace WMFFL\orm;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paid')]
class Paid
{

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'TeamID')]
    private Team $team;

    #[ORM\Column(type: 'integer')]
    private int $season;

    #[ORM\Column(type: 'float')]
    private float $previous;

    #[ORM\Column(name: 'entry_fee', type: 'float')]
    private float $entry;

    #[ORM\Column(type: 'float')]
    private float $late_fee;

    #[ORM\Column(type: 'boolean')]
    private bool $paid;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function setSeason(int $season): void
    {
        $this->season = $season;
    }

    public function getPrevious(): float
    {
        return $this->previous;
    }

    public function setPrevious(float $previous): void
    {
        $this->previous = $previous;
    }

    public function getEntry(): float
    {
        return $this->entry;
    }

    public function setEntry(float $entry): void
    {
        $this->entry = $entry;
    }

    public function getLateFee(): float
    {
        return $this->late_fee;
    }

    public function setLateFee(float $late_fee): void
    {
        $this->late_fee = $late_fee;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): void
    {
        $this->paid = $paid;
    }

}