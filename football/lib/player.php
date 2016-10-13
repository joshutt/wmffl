<?

class Transaction {
    var $date;
    var $event;
    var $team;

    function setDate($date) {$this->date = $date;}
    function setEvent($event) {$this->event = $event;}
    function setTeam($team) {$this->team = $team;}

    function getDate() {return $this->date;}
    function getEvent() {return $this->event;}
    function getTeam() {return $this->team;}

    function transcmp($a, $b) {
        $dateA = $a->getDate();
        $dateB = $b->getDate();
        if ($dateA == $dateB) return 0;
        return ($dateA < $dateB) ? -1 : 1;
    }

}




class Player {

    var $id;
    var $name;
    var $yearRet;
    var $team;
    var $position;
    var $transactions = array();
    var $lastUpdate;

    function Player ($id) {
        $this->id = $id;        
    }

    function setName ($name) {
        $this->name = $name;
    }

    function setYearRetired ($year) {
        $this->yearRet = $year;
    }

    function setPosition ($position) {
        $this->position = $position;
    }

    function setTeam ($team) {
        $this->team = $team;
    }

    function addTransaction ($newTrans) {
        array_push($this->transactions, $newTrans);
    }

    function getID() {return $this->id;}
    function getName() {return $this->name;}
    function getYearRetired() {return $this->yearRet;}
    function getPosNum() {return $this->position;}
    function getTeam() {return $this->NFLTeamMap[$this->team];}
    function getTeamNum() {return $this->team;}
    function getPosition() {
        $retVal = $this->PosMap[$this->position];
        if ($retVal) return $retVal;
        return $this->getPosNum();
    }

    //function transcmp($a, $b) {
    //    $dateA = $a->getDate();
    //    $dateB = $b->getDate();
    //    if ($dateA == $dateB) return 0;
    //    return ($dateA < $dateB) ? -1 : 1;
    //}

    function processTransactions() {
        //usort($this->transactions, "transcmp");
        usort($this->transactions, array("Transaction", "transcmp"));
        foreach ($this->transactions as $trans) {
            if ($trans->getDate() < $this->lastUpdate) continue;
            $event = $trans->getEvent();
            switch ($event) {
                case 1: case 7:
                    $this->team = $trans->getTeam();
                    $this->lastUpdate = $trans->getDate();                    
                    break;
                case 2:
                    $this->team = 0;
                    $this->lastUpdate = $trans->getDate();                    
            }
        }
    }
/*
    var $NFLTeamMap = array( 0=>"None", 1=>"Buffalo", 
                        2=>"Indianapolis", 3=>"Miami", 
                        4=>"New England", 5=>"NY Jets", 
                        6=>"Cincinnati", 7=>"Cleveland", 
                        8=>"Tennessee", 9=>"Jacksonville", 
                        10=>"Pittsburgh", 11=>"Denver", 
                        12=>"Kansas City", 13=>"Oakland", 
                        14=>"San Diego", 15=>"Seattle", 
                        16=>"Dallas", 17=>"NY Giant", 
                        18=>"Philadelphia", 19=>"Arizona", 
                        20=>"Washington", 21=>"Chicago", 
                        22=>"Detroit", 23=>"Green Bay", 
                        24=>"Minnesota", 25=>"Tampa Bay", 
                        26=>"Atlanta", 27=>"Carolina", 
                        28=>"St. Louis", 29=>"New Orleans", 
                        30=>"San Francisco", 31=>"Baltimore", 
                        32=>"Houston");
                        */
    var $NFLTeamMap = array( 0=>"None", 1=>"BUF", 
                        2=>"IND", 3=>"MIA", 
                        4=>"NE", 5=>"NYJ", 
                        6=>"CIN", 7=>"CLE", 
                        8=>"TEN", 9=>"JAC", 
                        10=>"PIT", 11=>"DEN", 
                        12=>"KC", 13=>"OAK", 
                        14=>"SD", 15=>"SEA", 
                        16=>"DAL", 17=>"NYG", 
                        18=>"PHI", 19=>"ARI", 
                        20=>"WAS", 21=>"CHI", 
                        22=>"DET", 23=>"GB", 
                        24=>"MIN", 25=>"TB", 
                        26=>"ATL", 27=>"CAR", 
                        28=>"LA", 29=>"NO", 
                        30=>"SF", 31=>"BAL", 
                        32=>"HOU");
        var $PosMap = array( 0=>"None", 1=>"HC", 2=>"QB", 3=>"RB", 4=>"WR",
                        5=>"TE", 6=>"K", 7=>"*", 8=>"OL", 9=>"*",
                        10=>"*", 11=>"*", 12=>"LB", 13=>"DB", 14=>"RB", 
                        15=>"DB", 16=>"DB",  17=>"DL", 18=>"DL", 19=>"*",  
                        20=>"*", 21=>"*", 22=>"*");
}


?>
