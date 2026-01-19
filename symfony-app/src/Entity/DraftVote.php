<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'draftvote')]
class DraftVote
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'userid', referencedColumnName: 'UserID')]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'lastUpdate', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastUpdate = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getLastUpdate(): ?\DateTime
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(?\DateTime $lastUpdate): static
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }
}
