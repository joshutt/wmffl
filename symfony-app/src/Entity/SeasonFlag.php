<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'season_flags')]
class SeasonFlag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'teamid', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Column(name: 'flags', length: 3, nullable: true)]
    private ?string $flags = null;

    #[ORM\Column(name: 'division_winner', type: 'boolean')]
    private ?bool $divisionWinner = false;

    #[ORM\Column(name: 'playoff_team', type: 'boolean')]
    private ?bool $playoffTeam = false;

    #[ORM\Column(name: 'finalist', type: 'boolean')]
    private ?bool $finalist = false;

    #[ORM\Column(name: 'champion', type: 'boolean')]
    private ?bool $champion = false;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getFlags(): ?string
    {
        return $this->flags;
    }

    public function setFlags(?string $flags): static
    {
        $this->flags = $flags;
        return $this;
    }

    public function isDivisionWinner(): ?bool
    {
        return $this->divisionWinner;
    }

    public function setDivisionWinner(bool $divisionWinner): static
    {
        $this->divisionWinner = $divisionWinner;
        return $this;
    }

    public function isPlayoffTeam(): ?bool
    {
        return $this->playoffTeam;
    }

    public function setPlayoffTeam(bool $playoffTeam): static
    {
        $this->playoffTeam = $playoffTeam;
        return $this;
    }

    public function isFinalist(): ?bool
    {
        return $this->finalist;
    }

    public function setFinalist(bool $finalist): static
    {
        $this->finalist = $finalist;
        return $this;
    }

    public function isChampion(): ?bool
    {
        return $this->champion;
    }

    public function setChampion(bool $champion): static
    {
        $this->champion = $champion;
        return $this;
    }
}
