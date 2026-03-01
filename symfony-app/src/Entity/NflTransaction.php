<?php

namespace App\Entity;

use App\Enum\NflTransactionActionEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nfltransactions')]
class NflTransaction
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Id]
    #[ORM\Column(name: 'transdate', type: Types::DATE_MUTABLE)]
    private ?\DateTime $transDate = null;

    #[ORM\Column(name: 'action', enumType: NflTransactionActionEnum::class)]
    private ?NflTransactionActionEnum $action = NflTransactionActionEnum::Unknown;

    #[ORM\Column(name: 'team', length: 3, nullable: true)]
    private ?string $team = null;

    #[ORM\Column(name: 'flag', type: 'integer', nullable: true)]
    private ?int $flag = null;

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getTransDate(): ?\DateTime
    {
        return $this->transDate;
    }

    public function setTransDate(\DateTime $transDate): static
    {
        $this->transDate = $transDate;
        return $this;
    }

    public function getAction(): ?NflTransactionActionEnum
    {
        return $this->action;
    }

    public function setAction(NflTransactionActionEnum $action): static
    {
        $this->action = $action;
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

    public function getFlag(): ?int
    {
        return $this->flag;
    }

    public function setFlag(?int $flag): static
    {
        $this->flag = $flag;
        return $this;
    }
}
