<?php


function convertHeight($inches) {
}

function resolveTeam($originalTeam) {
    switch ($originalTeam) {
        case 'BUF':
        case 'IND':
        case 'MIA':
        case 'NYJ':
        case 'CIN':
        case 'CLE':
        case 'TEN':
        case 'JAC':
        case 'PIT':
        case 'DEN':
        case 'SEA':
        case 'DAL':
        case 'NYG':
        case 'PHI':
        case 'ARI':
        case 'WAS':
        case 'CHI':
        case 'DET':
        case 'MIN':
        case 'ATL':
        case 'CAR':
        case 'BAL':
        case 'HOU':
            $teamVal = $originalTeam;
            break;
        case 'NEP':
            $teamVal = 'NE';
            break;
        case 'KCC':
            $teamVal = 'KC';
            break;
        case 'OAK':
        case 'LV':
        case 'LVR':
            $teamVal = 'LV';
            break;
        case 'SDC':
        case 'SD':
        case 'LAC':
            $teamVal = 'LAC';
            break;
        case 'GBP':
            $teamVal = 'GB';
            break;
        case 'TBB':
            $teamVal = 'TB';
            break;
        case 'NOS':
            $teamVal = 'NO';
            break;
        case 'SFO':
            $teamVal = 'SF';
            break;
        case 'RAM':
        case 'LAR':
        case 'LA':
            $teamVal = 'LAR';
            break;
        default:
            $teamVal = '';
    }
    return $teamVal;
}


function resolvePosition($pos) {
    switch ($pos) {
        case 'Off': $position = 'OL';  break;
        case 'QB':
        case 'RB':
        case 'WR':
        case 'TE':
        case 'LB':
                    $position = $pos;
                    break;
        case 'PK':  $position = 'K'; break;
        case 'DB': case 'CB': case 'S':
                    $position = 'DB';
                    break;
        case 'DE':  case 'DL':  case 'DT':
                    $position = 'DL';
                    break;
        case 'Coach':   case 'HC':
                    $position = 'HC';
                    break;
       default:    $position = '';
    }
    return $position;
}


Class Player {
    
    public $firstName;
    public $lastName;
    public $id;
    public $statId;
    public $pos;
    public $team;
    public $height;
    public $weight;
    public $birthday;
    public $number;
    public $college;
    public $draftInfo;
    public $extRefs;
    
    public function __construct() {
        $this->draftInfo = new DraftInfo();
        $this->extRefs = new ExternalRefs();
    }
    
    /** Assumes a player array from the load is being put in **/
    public static function buildFromArray($playerArray) {
        $player = new Player();
        
        $name = explode(",", $playerArray['name']);
        $player->lastName = $name[0];
        $player->firstName = trim($name[1]);
        $player->statId = $playerArray['id'];
        $player->pos = resolvePosition($playerArray['position']);
        $player->team = resolveTeam($playerArray['team']);
        $player->height = $playerArray['height'];
        $player->weight = $playerArray['weight'];
        
        // Draft Info
        $draftObj = new DraftInfo();
        $draftObj->year = $playerArray['draft_year'];
        $draftObj->team = resolveTeam($playerArray['draft_team']);
        $draftObj->round = $playerArray['draft_round'];
        $draftObj->pick = $playerArray['draft_pick'];
        $player->draftInfo = $draftObj;
         
        $status = $playerArray['status'];
        $player->birthday = (int) $playerArray['birthdate'];
        $player->number = $playerArray['jersey'];
        $player->college = $playerArray['college'];
         
        $statList = array('rotoworld', 'stats', 'kffl', 'sportsticker', 'cbs', 'fanball', 'espn');
        foreach ($statList as $statName) {
	        $player->extRefs->add_ref($statName, $playerArray[$statName.'_id']);
        }
         
        return $player;
    }
    
    
    public static function buildFromDatabase($playerArray) {
        $player = new Player();
        
        $player->id = $playerArray['playerid'];
        $player->lastName = $playerArray['lastname'];
        $player->firstName = $playerArray['firstname'];
        $player->statId = $playerArray['flmid'];
        $player->pos = $playerArray['pos'];
        $player->team = $playerArray['team'];
        $player->height = $playerArray['height'];
        $player->weight = $playerArray['weight'];
        
        // Draft Info
        $draftObj = $player->draftInfo;
        $draftObj->year = $playerArray['draftYear'];
        $draftObj->team = resolveTeam($playerArray['draftTeam']);
        $draftObj->round = $playerArray['draftRound'];
        $draftObj->pick = $playerArray['draftPick'];
        $player->draftInfo = $draftObj;
         
        $status = $playerArray['status'];
        $player->birthday = $playerArray['dob'];
        $player->number = $playerArray['number'];
        $player->college = $playerArray['college'];
         
        $statList = array('rotoworld', 'stats', 'kffl', 'sportsticker', 'cbs', 'fanball', 'espn');
        foreach ($statList as $statName) {
	        $player->extRefs->add_ref($statName, $playerArray[$statName.'_id']);
        }
         
        return $player;
    }
    
    
    public function getDisplayHeight() {
        if ($this->height == null) {
            return "";
        }
	    $feet = (int) ($this->height/12);
	    $inch = $this->height%12;
	    return "$feet' $inch\"";
    }
    
    public function getDisplayBirthday() {
        if ($this->birthday == null) {
            return "";
        }
        return date('m/d/Y', $this->birthday);
    }
    
    public function getSQLBirthday() {
        return date('Y-m-d', $this->birthday);
    }
}


class DraftInfo {
    
    public $year;
    public $team;
    public $round;
    public $pick;
    
    public function infoPresent() {
        if (isset($this->pick) && $this->pick > 0) {
            return true;
        } else {
            return false;
        }
    }
}


class ExternalRefs {
    
    var $refHash = array();
    
    function add_ref($refName, $refVal) {
        $this->refHash[$refName] = $refVal;
    }
    
    function get_ref($refName) {
        return $this->refHash[$refName];
    }
    
    function get_keys() {
        return array_keys($this->refHash);
    }
    
}
