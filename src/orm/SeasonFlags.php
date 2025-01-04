<?php
namespace WMFFL\orm;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'season_flags')]
class SeasonFlags
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $season;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private Team $team;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private string $flags;

    #[ORM\Column(name: 'division_winner', type: 'boolean')]
    private bool $divisionWinner = false;

    #[ORM\Column(name: 'playoff_team', type: 'boolean')]
    private bool $playoffTeam = false;

    #[ORM\Column(type: 'boolean')]
    private bool $finalist = false;

    #[ORM\Column(type: 'boolean')]
    private bool $champion = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeamid(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getFlags(): ?string
    {
        return $this->flags;
    }

    public function setFlags(?string $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function isDivisionWinner(): ?bool
    {
        return $this->divisionWinner;
    }

    public function setDivisionWinner(bool $divisionWinner): self
    {
        $this->divisionWinner = $divisionWinner;

        return $this;
    }

    public function isPlayoffTeam(): ?bool
    {
        return $this->playoffTeam;
    }

    public function setPlayoffTeam(bool $playoffTeam): self
    {
        $this->playoffTeam = $playoffTeam;

        return $this;
    }

    public function isFinalist(): ?bool
    {
        return $this->finalist;
    }

    public function setFinalist(bool $finalist): self
    {
        $this->finalist = $finalist;

        return $this;
    }

    public function isChampion(): ?bool
    {
        return $this->champion;
    }

    public function setChampion(bool $champion): self
    {
        $this->champion = $champion;

        return $this;
    }
}