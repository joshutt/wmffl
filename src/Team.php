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
    public ?array $allRef = null; // Reference to all teams, can be null initially
    public ?array $allRefKeys = null;

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

    public static function orderteam(self $a, self $b): int
    {
        if ($a === $b) {
            return 0;
        }
        if ($a->division < $b->division) {
            return -1;
        } elseif ($a->division > $b->division) {
            return 1;
        }
        if ($a->getWinPCT() > $b->getWinPCT()) {
            return -1;
        } elseif ($a->getWinPCT() < $b->getWinPCT()) {
            return 1;
        }
        $games = $a->games;
        /*
        print "<pre>";
        //print_r($games);
        print "</pre>";
        */
//        $h2h = array(0, 0, 0, 0, 0);
        $h2h = ['wins' => 0, 'losses' => 0, 'ties' => 0, 'ptsFor' => 0, 'ptsAgt' => 0];
        foreach ($games as $game) {
            if ($game[0] == $b->teamid) {
                $pFor = $game[1];
                $pAgt = $game[2];
                if ($pFor > $pAgt) {
                    $h2h['wins']++;
                } elseif ($pFor < $pAgt) {
                    $h2h['losses']++;
                } else {
                    $h2h['ties']++;
                }
                $h2h['ptsFor'] += $pFor;
                $h2h['ptsAgt'] += $pAgt;
            }
        }
        //print $a->name."-".$b->name."-".$h2h['wins']."-".$h2h['losses']."<br/>";
        if ($h2h['wins'] > $h2h['losses']) {
            return -1;
        } elseif ($h2h['wins'] < $h2h['losses']) {
            return 1;
        }

        if ($a->division == $b->division) {
            if ($a->getDivWinPCT() > $b->getDivWinPCT()) {
                return -1;
            } elseif ($a->getDivWinPCT() < $b->getDivWinPCT()) {
                return 1;
            }
        }
        /*
        if ($a->ptsFor-$a->ptsAgt > $b->ptsFor-$b->ptsAgt) {
            return -1;
        } elseif ($a->ptsFor-$a->ptsAgt < $b->ptsFor-$b->ptsAgt) {
            return 1;
        }
        */
        /*
        */

        /*
        print "<!-- Tie ".$a->name.": ".$a->getSOV($a->games)." <br/> ".$b->name.": ".$b->getSOV($b->games)." -->";
        print "<!-- {$a->name} games: ";
        print_r($a->games);
        print "-->";
        */

//    error_log(print_r($a, TRUE));
        if ($a->getSOV() > $b->getSOV()) {
            return -1;
        } elseif ($a->getSOV() < $b->getSOV()) {
            return 1;
        }

        if ($h2h['ptsFor'] > $h2h['ptsAgt']) {
            return -1;
        } elseif ($h2h['ptsFor'] < $h2h['ptsAgt']) {
            return 1;
        }
        if ($a->name > $b->name) {
            return 1;
        } elseif ($a->name < $b->name) {
            return -1;
        }
        return 0;
    }

    function addGame(int $opponentTeamId, ?int $ptsFor, ?int $ptsAgt, ?int $oppDiv): void
    {
        if ($ptsFor == '' || $ptsAgt == '') {
            return;
        }
//        print "here";
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

    function getSOV($teamArray = NULL): float
    {
//        return .500;
        if ($teamArray == NULL || !isset($teamArray)) {
            $teamArray = $this->allRef;
            if (empty($teamArray)) {
                return 0.000;
            }
        }
        //error_log(print_r($teamArray, true));
        $rec = array(0,0,0);
        $keyList = array_map('WMFFL\Team::getTeamId', $teamArray);
        $keyList = array_flip($keyList);
        // Loop through each game and if you won, add that teams record
//        error_log(print_r($keyList, true));
//        error_log(print_r($this->games, true));
        foreach ($this->games as $game) {
//            error_log("Game: ".$game[0]);
//            error_log("Key List: ". $keyList[$game[0]]);
//            error_log("Team Array: ".$teamArray[$keyList[$game[0]]]);
//            error_log("Team Record: ".$teamArray[$keyList[$game[0]]]->record);
            $teamRec = $teamArray[$keyList[$game[0]]]->record;
            foreach ($teamArray as $team) {
                if ($team->teamid == $game[0]) {
                    $teamRec = $team->record;
                    break;
                }
            }
            if ($game[1] > $game[2]) {
                $rec[0] += $teamRec[0];
                $rec[1] += $teamRec[1];
                $rec[2] += $teamRec[2];
            } elseif ($game[1] == $game[2]) {
                $rec[0] += $teamRec[0]/2.0;
                $rec[1] += $teamRec[1]/2.0;
                $rec[2] += $teamRec[2]/2.0;
            }
        }
        $gamePlay = $rec[0]+$rec[1]+$rec[2];
        if ($gamePlay == 0) return 0.00;
        $pct = ($rec[0] + $rec[2]/2.0) / $gamePlay;
        return $pct;
    }

    function getPrintSOV($teamArray) {
        $sov = $this->getSOV($teamArray);
        return sprintf('%5.3f', $sov);
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
