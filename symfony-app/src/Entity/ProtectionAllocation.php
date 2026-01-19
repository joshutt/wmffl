<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'protectionallocation')]
class ProtectionAllocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ProtectionID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private ?Team $team = null;

    #[ORM\Column(name: 'Season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Column(name: 'Special', type: 'smallint', nullable: true)]
    private ?int $special = null;

    #[ORM\Column(name: 'HC', type: 'smallint')]
    private ?int $hc = 0;

    #[ORM\Column(name: 'QB', type: 'smallint')]
    private ?int $qb = 0;

    #[ORM\Column(name: 'RB', type: 'smallint')]
    private ?int $rb = 0;

    #[ORM\Column(name: 'WR', type: 'smallint')]
    private ?int $wr = 0;

    #[ORM\Column(name: 'TE', type: 'smallint')]
    private ?int $te = 0;

    #[ORM\Column(name: 'K', type: 'smallint')]
    private ?int $k = 0;

    #[ORM\Column(name: 'OL', type: 'smallint')]
    private ?int $ol = 0;

    #[ORM\Column(name: 'DL', type: 'smallint')]
    private ?int $dl = 0;

    #[ORM\Column(name: 'LB', type: 'smallint')]
    private ?int $lb = 0;

    #[ORM\Column(name: 'DB', type: 'smallint')]
    private ?int $db = 0;

    #[ORM\Column(name: 'General', type: 'smallint')]
    private ?int $general = 0;

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

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getSpecial(): ?int
    {
        return $this->special;
    }

    public function setSpecial(?int $special): static
    {
        $this->special = $special;
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

    public function getRb(): ?int
    {
        return $this->rb;
    }

    public function setRb(int $rb): static
    {
        $this->rb = $rb;
        return $this;
    }

    public function getWr(): ?int
    {
        return $this->wr;
    }

    public function setWr(int $wr): static
    {
        $this->wr = $wr;
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

    public function getDl(): ?int
    {
        return $this->dl;
    }

    public function setDl(int $dl): static
    {
        $this->dl = $dl;
        return $this;
    }

    public function getLb(): ?int
    {
        return $this->lb;
    }

    public function setLb(int $lb): static
    {
        $this->lb = $lb;
        return $this;
    }

    public function getDb(): ?int
    {
        return $this->db;
    }

    public function setDb(int $db): static
    {
        $this->db = $db;
        return $this;
    }

    public function getGeneral(): ?int
    {
        return $this->general;
    }

    public function setGeneral(int $general): static
    {
        $this->general = $general;
        return $this;
    }
}
