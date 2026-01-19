<?php

namespace App\Entity;

use App\Enum\OfferStatusEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offer')]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'OfferID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamAID', referencedColumnName: 'TeamID')]
    private ?Team $teamA = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamBID', referencedColumnName: 'TeamID')]
    private ?Team $teamB = null;

    #[ORM\Column(name: 'Status', enumType: OfferStatusEnum::class)]
    private ?OfferStatusEnum $status = OfferStatusEnum::Pending;

    #[ORM\Column(name: 'Date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(name: 'LastOfferID', type: 'integer')]
    private ?int $lastOfferId = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamA(): ?Team
    {
        return $this->teamA;
    }

    public function setTeamA(?Team $teamA): static
    {
        $this->teamA = $teamA;
        return $this;
    }

    public function getTeamB(): ?Team
    {
        return $this->teamB;
    }

    public function setTeamB(?Team $teamB): static
    {
        $this->teamB = $teamB;
        return $this;
    }

    public function getStatus(): ?OfferStatusEnum
    {
        return $this->status;
    }

    public function setStatus(OfferStatusEnum $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getLastOfferId(): ?int
    {
        return $this->lastOfferId;
    }

    public function setLastOfferId(int $lastOfferId): static
    {
        $this->lastOfferId = $lastOfferId;
        return $this;
    }
}
