<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offeredplayers')]
class OfferedPlayer
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
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'PlayerID', referencedColumnName: 'playerid')]
    private ?Player $player = null;

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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }
}
