<?php

namespace App\Entity;

use App\Enum\ExpansionProtectionTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'expansionprotections')]
class ExpansionProtection
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Column(name: 'type', enumType: ExpansionProtectionTypeEnum::class)]
    private ?ExpansionProtectionTypeEnum $type = null;

    #[ORM\Column(name: 'protected', type: 'integer')]
    private ?int $protected = null;

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

    public function getType(): ?ExpansionProtectionTypeEnum
    {
        return $this->type;
    }

    public function setType(ExpansionProtectionTypeEnum $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getProtected(): ?int
    {
        return $this->protected;
    }

    public function setProtected(int $protected): static
    {
        $this->protected = $protected;
        return $this;
    }
}
