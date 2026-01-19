<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offeredpoints')]
class OfferedPoint
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Offer::class)]
    #[ORM\JoinColumn(name: 'OfferID', referencedColumnName: 'OfferID')]
    private ?Offer $offer = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamFromID', referencedColumnName: 'TeamID')]
    private ?Team $teamFrom = null;

    #[ORM\Id]
    #[ORM\Column(name: 'Season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'Points', type: 'smallint')]
    private ?int $points = null;

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;
        return $this;
    }

    public function getTeamFrom(): ?Team
    {
        return $this->teamFrom;
    }

    public function setTeamFrom(?Team $teamFrom): static
    {
        $this->teamFrom = $teamFrom;
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

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;
        return $this;
    }
}
