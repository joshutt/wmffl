<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'newinjuries')]
class NewInjury
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'week', type: 'integer')]
    private ?int $week = null;

    #[ORM\Column(name: 'status', length: 15)]
    private ?string $status = null;

    #[ORM\Column(name: 'details', length: 32, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(name: 'expectedReturn', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $expectedReturn = null;

    #[ORM\Column(name: 'version', length: 5, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(name: 'updated', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $updated = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): static
    {
        $this->week = $week;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;
        return $this;
    }

    public function getExpectedReturn(): ?\DateTime
    {
        return $this->expectedReturn;
    }

    public function setExpectedReturn(?\DateTime $expectedReturn): static
    {
        $this->expectedReturn = $expectedReturn;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;
        return $this;
    }

    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTime $updated): static
    {
        $this->updated = $updated;
        return $this;
    }
}
