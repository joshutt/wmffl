<?php

namespace WMFFL;

class Team {

    public string $name;
    public ?string $division; // Allow null if a team might not have a division initially
    public array $record;
    public array $divRecord; // Consider a specific class/enum for record structure
    public int $ptsFor;
    public int $ptsAgt;
    public int $divPtsFor;
    public int $divPtsAgt;
    public array $games; // Consider an array of Game objects
    public int $teamid;
    public float $sov = 0.0; // Pre-computed by StandingsCalculatorService before sorting

    public function __construct(string $newName, ?string $newDiv, int $newID)
    {
        $this->name = $newName;
        $this->division = $newDiv;
        $this->teamid = $newID;
        $this->record = [0, 0, 0]; // Wins, Losses, Ties
        $this->divRecord = [0, 0, 0];
        $this->ptsFor = 0;
        $this->ptsAgt = 0;
        $this->divPtsFor = 0;
        $this->divPtsAgt = 0;
        $this->games = [];
    }

    function addGame(int $opponentTeamId, ?int $ptsFor, ?int $ptsAgt, ?int $oppDiv): void
    {
        if ($ptsFor == '' || $ptsAgt == '') {
            return;
        }
        $this->games[] = array($opponentTeamId, $ptsFor, $ptsAgt);
        if ($this->division == $oppDiv) {
            $this->divPtsFor += $ptsFor;
            $this->divPtsAgt += $ptsAgt;
            if ($ptsFor > $ptsAgt) {
                $this->divRecord[0]++;
            } elseif ($ptsFor < $ptsAgt) {
                $this->divRecord[1]++;
            } else {
                $this->divRecord[2]++;
            }
        }
        $this->ptsFor += $ptsFor;
        $this->ptsAgt += $ptsAgt;
        if ($ptsFor > $ptsAgt) {
            $this->record[0]++;
        } elseif ($ptsFor < $ptsAgt) {
            $this->record[1]++;
        } else {
            $this->record[2]++;
        }
    }

    public function getWinPCT(): float
    {
        $totalGames = $this->record[0] + $this->record[1] + $this->record[2];
        if ($totalGames === 0) {
            return 0.000;
        }
        return ($this->record[0] + ($this->record[2] / 2.0)) / $totalGames;
    }

    function getDivWinPCT(): float
    {
        if ($this->divRecord[0]+$this->divRecord[2] == 0) {
            return 0.000;
        } else {
            return ($this->divRecord[0] + $this->divRecord[2]/2.0)/($this->divRecord[0]+$this->divRecord[1]+$this->divRecord[2]);
        }
    }

    function getPrintRecord() {
        return sprintf('%s &nbsp;&nbsp;&nbsp;%5.3f', $this->printShortRecord(), $this->getWinPCT());
    }

    function printShortRecord()
    {
        if ($this->record[2] > 0) {
            return sprintf('%d - %d - %d', $this->record[0], $this->record[1], $this->record[2]);
        } else {
            return sprintf('%d - %d', $this->record[0], $this->record[1]);
        }
    }

    public static function getTeamId($t)
    {
        return $t->teamid;
    }
}
