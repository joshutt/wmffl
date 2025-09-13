<?php

namespace App\Entity;

use App\Enum\PosEnum;
use App\Repository\PlayerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Table(name: 'newplayers')]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'playerid')]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $flmid = null;

    #[ORM\Column(length: 25)]
    private ?string $lastname = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(nullable: true, enumType: PosEnum::class)]
    private ?PosEnum $pos = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $team = null;

    #[ORM\Column(nullable: true)]
    private ?int $number = null;

    #[ORM\Column(nullable: true)]
    private ?int $retired = null;

    #[ORM\Column(nullable: true)]
    private ?int $height = null;

    #[ORM\Column(nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dob = null;

    #[ORM\Column(name: 'draftTeam', length: 3, nullable: true)]
    private ?string $draftTeam = null;

    #[ORM\Column(name: 'draftYear', nullable: true)]
    private ?int $draftYear = null;

    #[ORM\Column(name: 'draftRound', nullable: true)]
    private ?int $draftRound = null;

    #[ORM\Column(name: 'draftPick', nullable: true)]
    private ?int $draftPick = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(name: 'usePos')]
    private ?bool $usePos = null;

    #[ORM\Column(nullable: true)]
    private ?int $nflid = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $nfldbId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFlmid(): ?int
    {
        return $this->flmid;
    }

    public function setFlmid(int $flmid): static
    {
        $this->flmid = $flmid;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPos(): ?PosEnum
    {
        return $this->pos;
    }

    public function setPos(?PosEnum $pos): static
    {
        $this->pos = $pos;

        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setTeam(?string $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getRetired(): ?int
    {
        return $this->retired;
    }

    public function setRetired(?int $retired): static
    {
        $this->retired = $retired;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getDob(): ?\DateTime
    {
        return $this->dob;
    }

    public function setDob(?\DateTime $dob): static
    {
        $this->dob = $dob;

        return $this;
    }

    public function getDraftTeam(): ?string
    {
        return $this->draftTeam;
    }

    public function setDraftTeam(?string $draftTeam): static
    {
        $this->draftTeam = $draftTeam;

        return $this;
    }

    public function getDraftYear(): ?int
    {
        return $this->draftYear;
    }

    public function setDraftYear(?int $draftYear): static
    {
        $this->draftYear = $draftYear;

        return $this;
    }

    public function getDraftRound(): ?int
    {
        return $this->draftRound;
    }

    public function setDraftRound(?int $draftRound): static
    {
        $this->draftRound = $draftRound;

        return $this;
    }

    public function getDraftPick(): ?int
    {
        return $this->draftPick;
    }

    public function setDraftPick(?int $draftPick): static
    {
        $this->draftPick = $draftPick;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isUsePos(): ?bool
    {
        return $this->usePos;
    }

    public function setUsePos(bool $usePos): static
    {
        $this->usePos = $usePos;

        return $this;
    }

    public function getNflid(): ?int
    {
        return $this->nflid;
    }

    public function setNflid(?int $nflid): static
    {
        $this->nflid = $nflid;

        return $this;
    }

    public function getNfldbId(): ?string
    {
        return $this->nfldbId;
    }

    public function setNfldbId(?string $nfldbId): static
    {
        $this->nfldbId = $nfldbId;

        return $this;
    }
}
