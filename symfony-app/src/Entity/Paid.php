<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paid')]
class Paid
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'TeamID', nullable: true)]
    private ?Team $team = null;

    #[ORM\Column(name: 'season', type: 'integer', nullable: true)]
    private ?int $season = null;

    #[ORM\Column(name: 'previous', type: 'float', nullable: true)]
    private ?float $previous = 0;

    #[ORM\Column(name: 'entry_fee', type: 'float', nullable: true)]
    private ?float $entryFee = 75;

    #[ORM\Column(name: 'late_fee', type: 'float', nullable: true)]
    private ?float $lateFee = 0;

    #[ORM\Column(name: 'paid', type: 'boolean', nullable: true)]
    private ?bool $paid = true;

    #[ORM\Column(name: 'amtPaid', type: 'float')]
    private ?float $amtPaid = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function setSeason(?int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getPrevious(): ?float
    {
        return $this->previous;
    }

    public function setPrevious(?float $previous): static
    {
        $this->previous = $previous;
        return $this;
    }

    public function getEntryFee(): ?float
    {
        return $this->entryFee;
    }

    public function setEntryFee(?float $entryFee): static
    {
        $this->entryFee = $entryFee;
        return $this;
    }

    public function getLateFee(): ?float
    {
        return $this->lateFee;
    }

    public function setLateFee(?float $lateFee): static
    {
        $this->lateFee = $lateFee;
        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(?bool $paid): static
    {
        $this->paid = $paid;
        return $this;
    }

    public function getAmtPaid(): ?float
    {
        return $this->amtPaid;
    }

    public function setAmtPaid(float $amtPaid): static
    {
        $this->amtPaid = $amtPaid;
        return $this;
    }
}
