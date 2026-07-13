<?php

namespace App\Entity;

use App\Enum\OfferCommentActionEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A free-text comment written during the trade flow (offer, amend,
 * counter, accept, reject, withdraw, admin void). Legacy only pasted
 * these into the notification email; they are now persisted and shown
 * as a running history on the trade screen.
 */
#[ORM\Entity]
#[ORM\Table(name: 'offercomments')]
class OfferComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CommentID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Offer::class)]
    #[ORM\JoinColumn(name: 'OfferID', referencedColumnName: 'OfferID')]
    private ?Offer $offer = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Column(name: 'Action', enumType: OfferCommentActionEnum::class)]
    private ?OfferCommentActionEnum $action = null;

    #[ORM\Column(name: 'Date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(name: 'Comment', type: Types::TEXT)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;
        return $this;
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

    public function getAction(): ?OfferCommentActionEnum
    {
        return $this->action;
    }

    public function setAction(OfferCommentActionEnum $action): static
    {
        $this->action = $action;
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }
}
