<?

class Transaction {
    var $date;
    var $action;
    var $team;
    var $drafted;
}


class Player {

    var $dbid;
    var $flmid;
    var $name;
    var $pos;
    var $team;
    var $number;
    var $retired;
    var $height;
    var $weight;
    var $dob;
    var $draftTeam;
    var $draftYear;
    var $draftRound;
    var $draftPick;
    var $active;
    var $nflid;
    var $transactions;

    function Player() {
        $this->transactions = array();
    }

    function addTransaction($trans) {
        if (is_a($trans, "Transaction")) {
            array_push($this->transactions, $trans);
            usort($this->transactions, "transDateSort");
        }
    }
}



function posLabel($pos) {

    switch($pos) {
        case 0: return "None"; break;
        case 1: return "HC"; break;
        case 2: return "QB"; break;
        case 3:
        case 14:
            return "RB"; break;
        case 4: return "WR"; break;
        case 5: return "TE"; break;
        case 6: return "K"; break;
        case 8: return "OL"; break;
        case 12: return "LB"; break;
        case 13:
        case 15:
        case 16:
            return "DB"; break;
        case 17:
        case 18:
            return "DL"; break;
        default:
            return ""; break;
    }
}

function nflLabel($teamNum) {
    switch($teamNum) {
        case 1: return 'BUF'; break;
        case 2: return 'IND'; break;
        case 3: return 'MIA'; break;
        case 4: return 'NE'; break;
        case 5: return 'NYJ'; break;
        case 6: return 'CIN'; break;
        case 7: return 'CLE'; break;
        case 8: return 'TEN'; break;
        case 9: return 'JAC'; break;
        case 10: return 'PIT'; break;
        case 11: return 'DEN'; break;
        case 12: return 'KC'; break;
        case 13: return 'OAK'; break;
        case 14: return 'LAC'; break;
        case 15: return 'SEA'; break;
        case 16: return 'DAL'; break;
        case 17: return 'NYG'; break;
        case 18: return 'PHI'; break;
        case 19: return 'ARI'; break;
        case 20: return 'WAS'; break;
        case 21: return 'CHI'; break;
        case 22: return 'DET'; break;
        case 23: return 'GB'; break;
        case 24: return 'MIN'; break;
        case 25: return 'TB'; break;
        case 26: return 'ATL'; break;
        case 27: return 'CAR'; break;
        case 28: return 'LAR'; break;
        case 29: return 'NO'; break;
        case 30: return 'SF'; break;
        case 31: return 'BAL'; break;
        case 32: return 'HOU'; break;
    }
}

function transDateSort($a, $b) {
    if ($a == $b || $a->date == $b->date) {
        return 0;
    }
    
    $datea = $a->date;
    $dateb = $b->date;
    return $datea[0] < $dateb[0] ? -1 : 1;
}

?>
