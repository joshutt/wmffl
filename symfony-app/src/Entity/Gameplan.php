<?php

namespace App\Entity;

use App\Enum\GameplanSideEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * Gameplan is a retired feature: as of Phase 15 (2026-08) nothing in the
 * application writes a gameplan row, and the activations pages that used
 * to no longer mention it.
 *
 * This mapping is kept ON PURPOSE. The ~1,300 historical rows are league
 * record, still rendered as the GP+/GP- markers on the box score
 * (football/activate/scoreFunctions.php) and still joined by
 * scripts/livescore/updatescores.php, and a later phase that surfaces
 * gameplan history will want the entity already in place. It is not dead
 * code awaiting a sweep - do not delete it, or App\Enum\GameplanSideEnum,
 * without an explicit decision to drop the data too.
 */
#[ORM\Entity]
#[ORM\Table(name: 'gameplan')]
class Gameplan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'gameplan_id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'week', type: 'integer')]
    private ?int $week = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: 'playerid', referencedColumnName: 'playerid')]
    private ?Player $player = null;

    #[ORM\Column(name: 'side', enumType: GameplanSideEnum::class)]
    private ?GameplanSideEnum $side = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSide(): ?GameplanSideEnum
    {
        return $this->side;
    }

    public function setSide(GameplanSideEnum $side): static
    {
        $this->side = $side;
        return $this;
    }
}
