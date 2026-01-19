<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Maps to the teamnames table
 * Stores team names for each season (allows name changes)
 */
#[ORM\Entity]
#[ORM\Table(name: 'teamnames')]
class TeamNames
{
    #[ORM\Id]
    #[ORM\Column(name: 'teamid', type: Types::INTEGER)]
    private int $teamId;

    #[ORM\Id]
    #[ORM\Column(name: 'season', type: Types::INTEGER)]
    private int $season;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 100)]
    private string $name;

    #[ORM\Column(name: 'divisionid', type: Types::INTEGER)]
    private int $divisionId;

    public function getTeamId(): int
    {
        return $this->teamId;
    }

    public function setTeamId(int $teamId): static
    {
        $this->teamId = $teamId;
        return $this;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    public function setDivisionId(int $divisionId): static
    {
        $this->divisionId = $divisionId;
        return $this;
    }
}
