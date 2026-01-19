<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stats')]
class Stat
{
    #[ORM\Id]
    #[ORM\Column(name: 'statid', type: 'integer')]
    private ?int $statId = null;

    #[ORM\Id]
    #[ORM\Column(name: 'Season', type: 'integer')]
    private ?int $season = null;

    #[ORM\Id]
    #[ORM\Column(name: 'week', type: 'integer')]
    private ?int $week = null;

    #[ORM\Column(name: 'played', type: 'smallint')]
    private ?int $played = 1;

    #[ORM\Column(name: 'yards', type: 'integer')]
    private ?int $yards = 0;

    #[ORM\Column(name: 'intthrow', type: 'integer')]
    private ?int $intThrow = 0;

    #[ORM\Column(name: 'rec', type: 'integer')]
    private ?int $rec = 0;

    #[ORM\Column(name: 'fum', type: 'integer')]
    private ?int $fum = 0;

    #[ORM\Column(name: 'tackles', type: 'integer')]
    private ?int $tackles = 0;

    #[ORM\Column(name: 'sacks', type: 'float')]
    private ?float $sacks = 0;

    #[ORM\Column(name: 'intcatch', type: 'integer')]
    private ?int $intCatch = 0;

    #[ORM\Column(name: 'passdefend', type: 'integer')]
    private ?int $passDefend = 0;

    #[ORM\Column(name: 'returnyards', type: 'integer')]
    private ?int $returnYards = 0;

    #[ORM\Column(name: 'fumrec', type: 'integer')]
    private ?int $fumRec = 0;

    #[ORM\Column(name: 'forcefum', type: 'integer')]
    private ?int $forceFum = 0;

    #[ORM\Column(name: 'tds', type: 'integer')]
    private ?int $tds = 0;

    #[ORM\Column(name: '2pt', type: 'integer')]
    private ?int $twoPt = 0;

    #[ORM\Column(name: 'specTD', type: 'integer')]
    private ?int $specTD = 0;

    #[ORM\Column(name: 'Safety', type: 'integer')]
    private ?int $safety = 0;

    #[ORM\Column(name: 'XP', type: 'integer')]
    private ?int $xp = 0;

    #[ORM\Column(name: 'MissXP', type: 'integer')]
    private ?int $missXP = 0;

    #[ORM\Column(name: 'FG30', type: 'integer')]
    private ?int $fg30 = 0;

    #[ORM\Column(name: 'FG40', type: 'integer')]
    private ?int $fg40 = 0;

    #[ORM\Column(name: 'FG50', type: 'integer')]
    private ?int $fg50 = 0;

    #[ORM\Column(name: 'FG60', type: 'integer')]
    private ?int $fg60 = 0;

    #[ORM\Column(name: 'MissFG30', type: 'integer')]
    private ?int $missFG30 = 0;

    #[ORM\Column(name: 'ptdiff', type: 'integer', nullable: true)]
    private ?int $ptDiff = null;

    #[ORM\Column(name: 'blockpunt', type: 'integer')]
    private ?int $blockPunt = 0;

    #[ORM\Column(name: 'blockfg', type: 'integer')]
    private ?int $blockFG = 0;

    #[ORM\Column(name: 'blockxp', type: 'integer')]
    private ?int $blockXP = 0;

    #[ORM\Column(name: 'penalties', type: 'integer')]
    private ?int $penalties = 0;

    public function getStatId(): ?int
    {
        return $this->statId;
    }

    public function setStatId(int $statId): static
    {
        $this->statId = $statId;
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

    public function getPlayed(): ?int
    {
        return $this->played;
    }

    public function setPlayed(int $played): static
    {
        $this->played = $played;
        return $this;
    }

    public function getYards(): ?int
    {
        return $this->yards;
    }

    public function setYards(int $yards): static
    {
        $this->yards = $yards;
        return $this;
    }

    public function getIntThrow(): ?int
    {
        return $this->intThrow;
    }

    public function setIntThrow(int $intThrow): static
    {
        $this->intThrow = $intThrow;
        return $this;
    }

    public function getRec(): ?int
    {
        return $this->rec;
    }

    public function setRec(int $rec): static
    {
        $this->rec = $rec;
        return $this;
    }

    public function getFum(): ?int
    {
        return $this->fum;
    }

    public function setFum(int $fum): static
    {
        $this->fum = $fum;
        return $this;
    }

    public function getTackles(): ?int
    {
        return $this->tackles;
    }

    public function setTackles(int $tackles): static
    {
        $this->tackles = $tackles;
        return $this;
    }

    public function getSacks(): ?float
    {
        return $this->sacks;
    }

    public function setSacks(float $sacks): static
    {
        $this->sacks = $sacks;
        return $this;
    }

    public function getIntCatch(): ?int
    {
        return $this->intCatch;
    }

    public function setIntCatch(int $intCatch): static
    {
        $this->intCatch = $intCatch;
        return $this;
    }

    public function getPassDefend(): ?int
    {
        return $this->passDefend;
    }

    public function setPassDefend(int $passDefend): static
    {
        $this->passDefend = $passDefend;
        return $this;
    }

    public function getReturnYards(): ?int
    {
        return $this->returnYards;
    }

    public function setReturnYards(int $returnYards): static
    {
        $this->returnYards = $returnYards;
        return $this;
    }

    public function getFumRec(): ?int
    {
        return $this->fumRec;
    }

    public function setFumRec(int $fumRec): static
    {
        $this->fumRec = $fumRec;
        return $this;
    }

    public function getForceFum(): ?int
    {
        return $this->forceFum;
    }

    public function setForceFum(int $forceFum): static
    {
        $this->forceFum = $forceFum;
        return $this;
    }

    public function getTds(): ?int
    {
        return $this->tds;
    }

    public function setTds(int $tds): static
    {
        $this->tds = $tds;
        return $this;
    }

    public function getTwoPt(): ?int
    {
        return $this->twoPt;
    }

    public function setTwoPt(int $twoPt): static
    {
        $this->twoPt = $twoPt;
        return $this;
    }

    public function getSpecTD(): ?int
    {
        return $this->specTD;
    }

    public function setSpecTD(int $specTD): static
    {
        $this->specTD = $specTD;
        return $this;
    }

    public function getSafety(): ?int
    {
        return $this->safety;
    }

    public function setSafety(int $safety): static
    {
        $this->safety = $safety;
        return $this;
    }

    public function getXp(): ?int
    {
        return $this->xp;
    }

    public function setXp(int $xp): static
    {
        $this->xp = $xp;
        return $this;
    }

    public function getMissXP(): ?int
    {
        return $this->missXP;
    }

    public function setMissXP(int $missXP): static
    {
        $this->missXP = $missXP;
        return $this;
    }

    public function getFg30(): ?int
    {
        return $this->fg30;
    }

    public function setFg30(int $fg30): static
    {
        $this->fg30 = $fg30;
        return $this;
    }

    public function getFg40(): ?int
    {
        return $this->fg40;
    }

    public function setFg40(int $fg40): static
    {
        $this->fg40 = $fg40;
        return $this;
    }

    public function getFg50(): ?int
    {
        return $this->fg50;
    }

    public function setFg50(int $fg50): static
    {
        $this->fg50 = $fg50;
        return $this;
    }

    public function getFg60(): ?int
    {
        return $this->fg60;
    }

    public function setFg60(int $fg60): static
    {
        $this->fg60 = $fg60;
        return $this;
    }

    public function getMissFG30(): ?int
    {
        return $this->missFG30;
    }

    public function setMissFG30(int $missFG30): static
    {
        $this->missFG30 = $missFG30;
        return $this;
    }

    public function getPtDiff(): ?int
    {
        return $this->ptDiff;
    }

    public function setPtDiff(?int $ptDiff): static
    {
        $this->ptDiff = $ptDiff;
        return $this;
    }

    public function getBlockPunt(): ?int
    {
        return $this->blockPunt;
    }

    public function setBlockPunt(int $blockPunt): static
    {
        $this->blockPunt = $blockPunt;
        return $this;
    }

    public function getBlockFG(): ?int
    {
        return $this->blockFG;
    }

    public function setBlockFG(int $blockFG): static
    {
        $this->blockFG = $blockFG;
        return $this;
    }

    public function getBlockXP(): ?int
    {
        return $this->blockXP;
    }

    public function setBlockXP(int $blockXP): static
    {
        $this->blockXP = $blockXP;
        return $this;
    }

    public function getPenalties(): ?int
    {
        return $this->penalties;
    }

    public function setPenalties(int $penalties): static
    {
        $this->penalties = $penalties;
        return $this;
    }
}
