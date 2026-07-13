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

    /**
     * Legacy quirk: the LastOfferID column stores the id of the TEAM that
     * made the most recent offer (it drives whose turn it is), not an
     * offer id. The accessors are named for what it actually holds.
     */
    #[ORM\Column(name: 'LastOfferID', type: 'integer')]
    private ?int $lastOfferTeamId = 0;

    /**
     * Amend/counter creates a new offer row; this links it back to the
     * offer it replaced so comment history can follow the negotiation.
     * Legacy rows stay NULL.
     */
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'PrevOfferID', referencedColumnName: 'OfferID', nullable: true)]
    private ?Offer $prevOffer = null;

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

    public function getLastOfferTeamId(): ?int
    {
        return $this->lastOfferTeamId;
    }

    public function setLastOfferTeamId(int $lastOfferTeamId): static
    {
        $this->lastOfferTeamId = $lastOfferTeamId;
        return $this;
    }

    public function getPrevOffer(): ?Offer
    {
        return $this->prevOffer;
    }

    public function setPrevOffer(?Offer $prevOffer): static
    {
        $this->prevOffer = $prevOffer;
        return $this;
    }
}
