<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offeredpicks')]
class OfferedPick
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
    #[ORM\Column(name: 'Round', type: 'smallint')]
    private ?int $round = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'OrgTeam', referencedColumnName: 'TeamID', nullable: true)]
    private ?Team $orgTeam = null;

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

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function setRound(int $round): static
    {
        $this->round = $round;
        return $this;
    }

    public function getOrgTeam(): ?Team
    {
        return $this->orgTeam;
    }

    public function setOrgTeam(?Team $orgTeam): static
    {
        $this->orgTeam = $orgTeam;
        return $this;
    }
}
