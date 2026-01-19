<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activations')]
class Activation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Id]
    #[ORM\Column(name: 'Season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'Week', type: 'smallint')]
    private ?int $week = null;

    #[ORM\Column(name: 'HC', type: 'integer')]
    private ?int $hc = 0;

    #[ORM\Column(name: 'QB', type: 'integer')]
    private ?int $qb = 0;

    #[ORM\Column(name: 'RB1', type: 'integer')]
    private ?int $rb1 = 0;

    #[ORM\Column(name: 'RB2', type: 'integer')]
    private ?int $rb2 = 0;

    #[ORM\Column(name: 'WR1', type: 'integer')]
    private ?int $wr1 = 0;

    #[ORM\Column(name: 'WR2', type: 'integer')]
    private ?int $wr2 = 0;

    #[ORM\Column(name: 'TE', type: 'integer')]
    private ?int $te = 0;

    #[ORM\Column(name: 'K', type: 'integer')]
    private ?int $k = 0;

    #[ORM\Column(name: 'OL', type: 'integer')]
    private ?int $ol = 0;

    #[ORM\Column(name: 'DL1', type: 'integer')]
    private ?int $dl1 = 0;

    #[ORM\Column(name: 'DL2', type: 'integer')]
    private ?int $dl2 = 0;

    #[ORM\Column(name: 'LB1', type: 'integer')]
    private ?int $lb1 = 0;

    #[ORM\Column(name: 'LB2', type: 'integer')]
    private ?int $lb2 = 0;

    #[ORM\Column(name: 'DB1', type: 'integer')]
    private ?int $db1 = 0;

    #[ORM\Column(name: 'DB2', type: 'integer')]
    private ?int $db2 = 0;

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
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

    public function getHc(): ?int
    {
        return $this->hc;
    }

    public function setHc(int $hc): static
    {
        $this->hc = $hc;
        return $this;
    }

    public function getQb(): ?int
    {
        return $this->qb;
    }

    public function setQb(int $qb): static
    {
        $this->qb = $qb;
        return $this;
    }

    public function getRb1(): ?int
    {
        return $this->rb1;
    }

    public function setRb1(int $rb1): static
    {
        $this->rb1 = $rb1;
        return $this;
    }

    public function getRb2(): ?int
    {
        return $this->rb2;
    }

    public function setRb2(int $rb2): static
    {
        $this->rb2 = $rb2;
        return $this;
    }

    public function getWr1(): ?int
    {
        return $this->wr1;
    }

    public function setWr1(int $wr1): static
    {
        $this->wr1 = $wr1;
        return $this;
    }

    public function getWr2(): ?int
    {
        return $this->wr2;
    }

    public function setWr2(int $wr2): static
    {
        $this->wr2 = $wr2;
        return $this;
    }

    public function getTe(): ?int
    {
        return $this->te;
    }

    public function setTe(int $te): static
    {
        $this->te = $te;
        return $this;
    }

    public function getK(): ?int
    {
        return $this->k;
    }

    public function setK(int $k): static
    {
        $this->k = $k;
        return $this;
    }

    public function getOl(): ?int
    {
        return $this->ol;
    }

    public function setOl(int $ol): static
    {
        $this->ol = $ol;
        return $this;
    }

    public function getDl1(): ?int
    {
        return $this->dl1;
    }

    public function setDl1(int $dl1): static
    {
        $this->dl1 = $dl1;
        return $this;
    }

    public function getDl2(): ?int
    {
        return $this->dl2;
    }

    public function setDl2(int $dl2): static
    {
        $this->dl2 = $dl2;
        return $this;
    }

    public function getLb1(): ?int
    {
        return $this->lb1;
    }

    public function setLb1(int $lb1): static
    {
        $this->lb1 = $lb1;
        return $this;
    }

    public function getLb2(): ?int
    {
        return $this->lb2;
    }

    public function setLb2(int $lb2): static
    {
        $this->lb2 = $lb2;
        return $this;
    }

    public function getDb1(): ?int
    {
        return $this->db1;
    }

    public function setDb1(int $db1): static
    {
        $this->db1 = $db1;
        return $this;
    }

    public function getDb2(): ?int
    {
        return $this->db2;
    }

    public function setDb2(int $db2): static
    {
        $this->db2 = $db2;
        return $this;
    }
}
