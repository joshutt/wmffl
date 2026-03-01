<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'trade')]
class Trade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TradeID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamFromID', referencedColumnName: 'TeamID')]
    private ?Team $teamFrom = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamToID', referencedColumnName: 'TeamID')]
    private ?Team $teamTo = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'PlayerID', referencedColumnName: 'playerid', nullable: true)]
    private ?Player $player = null;

    #[ORM\Column(name: 'Other', type: Types::TEXT, nullable: true)]
    private ?string $other = null;

    #[ORM\Column(name: 'Date', type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(name: 'TradeGroup', type: 'integer')]
    private ?int $tradeGroup = 0;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTeamTo(): ?Team
    {
        return $this->teamTo;
    }

    public function setTeamTo(?Team $teamTo): static
    {
        $this->teamTo = $teamTo;
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

    public function getOther(): ?string
    {
        return $this->other;
    }

    public function setOther(?string $other): static
    {
        $this->other = $other;
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

    public function getTradeGroup(): ?int
    {
        return $this->tradeGroup;
    }

    public function setTradeGroup(int $tradeGroup): static
    {
        $this->tradeGroup = $tradeGroup;
        return $this;
    }
}
