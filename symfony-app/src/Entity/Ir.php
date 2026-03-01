<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ir')]
class Ir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Column(name: 'current', type: 'boolean')]
    private ?bool $current = false;

    #[ORM\Column(name: 'dateon', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateOn = null;

    #[ORM\Column(name: 'dateoff', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateOff = null;

    #[ORM\Column(name: 'covid', type: 'boolean')]
    private ?bool $covid = false;

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

    public function isCurrent(): ?bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): static
    {
        $this->current = $current;
        return $this;
    }

    public function getDateOn(): ?\DateTime
    {
        return $this->dateOn;
    }

    public function setDateOn(\DateTime $dateOn): static
    {
        $this->dateOn = $dateOn;
        return $this;
    }

    public function getDateOff(): ?\DateTime
    {
        return $this->dateOff;
    }

    public function setDateOff(?\DateTime $dateOff): static
    {
        $this->dateOff = $dateOff;
        return $this;
    }

    public function isCovid(): ?bool
    {
        return $this->covid;
    }

    public function setCovid(bool $covid): static
    {
        $this->covid = $covid;
        return $this;
    }
}
