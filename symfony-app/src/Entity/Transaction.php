<?php

namespace App\Entity;

use App\Enum\TransactionMethodEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transactions')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TransactionID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'PlayerID', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Column(name: 'Method', enumType: TransactionMethodEnum::class)]
    private ?TransactionMethodEnum $method = TransactionMethodEnum::Cut;

    #[ORM\Column(name: 'Date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getMethod(): ?TransactionMethodEnum
    {
        return $this->method;
    }

    public function setMethod(TransactionMethodEnum $method): static
    {
        $this->method = $method;
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
}
