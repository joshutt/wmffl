<?php
require_once 'playerHelpers.php';
require_once 'config.php';

function test($player) {
    $a = new Player();
    var_dump($a);
}


function evaluatePlayer(Player $player) {
    // Query player by statid.
//    getPlayerByStatId(2705);        // Test this is Terrell Owens
    //getPlayerByStatId($player->statId);
    $exists = checkForStatId($player->statId);
    //$exists = checkForStatId(99999);
    
    if ($exists) {
        $success = updateExisting($player);
    } else {
        $sucess = insertNew($player);
    }
    //print "<p> Player Id: {$player->id}</p>";
    updateRoster($player);
}


/**
 * Insert a player that does not currently exist in the database into the database.
 * @param unknown_type $player
 */
function insertNew(Player $player) {
    $firstName = mysql_real_escape_string($player->firstName);
    $lastName = mysql_real_escape_string($player->lastName);
    $baseQuery = "INSERT INTO newplayers (flmid, lastname, firstname, pos, team";
    $baseValues = "{$player->statId}, '$lastName', '$firstName', '{$player->pos}', '{$player->team}'";
    
    if ($player->number != 0) {
        $baseQuery .= ", number";
        $baseValues .= ", ".$player->number;
    }
    
    if ($player->height != 0) {
        $baseQuery .= ", height";
        $baseValues .= ", ".$player->height;
    }
    
    if ($player->weight != 0) {
        $baseQuery .= ", weight";
        $baseValues .= ", ".$player->weight;
    }
    
    if ($player->birthday != 0) {
        $baseQuery .= ", dob";
        $baseValues .= ", '".$player->getSQLBirthday()."'";
    }
    
    $draftInfo = $player->draftInfo;
    if ($draftInfo->infoPresent()) {
        $baseQuery .= ", draftTeam, draftYear, draftRound, draftPick"; 
        $baseValues .= ", '{$draftInfo->team}', {$draftInfo->year}, {$draftInfo->round}, {$draftInfo->pick}";
    }
    
    $finalQuery = "$baseQuery, active) VALUES ($baseValues, 1)";
    $result = mysql_query($finalQuery) or die("Unable to insert [{$player->statId}] - ".mysql_error());
    $numRows = mysql_affected_rows();
    $newId = mysql_insert_id();
    $player->id = $newId;
    
    print "Adding {$player->firstName} {$player->lastName}\n";
    return TRUE;
}


/**
 * 
 * Update an existing player in the database
 * @param Player $player
 */
function updateExisting(Player $player) {
    $baseQuery = "UPDATE newplayers ";
    
    $firstName = mysql_real_escape_string($player->firstName);
    $lastName = mysql_real_escape_string($player->lastName);
    $baseQuery .= "SET lastname='$lastName', firstname='$firstName', pos='{$player->pos}'";
    $baseQuery .= ", active=1, team='{$player->team}'";
    
    if ($player->number != 0) {
        $baseQuery .= ", number={$player->number}";
    } else {
        $baseQuery .= ", number=null";
    }
    
    if ($player->height != 0) {
        $baseQuery .= ", height={$player->height}";
    } else {
        $baseQuery .= ", height=null";
    }
    
    if ($player->weight != 0) {
        $baseQuery .= ", weight={$player->weight}";
    } else {
        $baseQuery .= ", weight=null";
    }
    
    if ($player->birthday != 0) {
        $baseQuery .= ", dob='{$player->getSQLBirthday()}'";
    } else {
        $baseQuery .= ", dob=null";
    }
    
    $draftInfo = $player->draftInfo;
    if ($draftInfo->infoPresent()) {
        $baseQuery .= ", draftTeam='{$draftInfo->team}', draftYear={$draftInfo->year}";
        $baseQuery .= ", draftRound={$draftInfo->round}, draftPick={$draftInfo->pick}"; 
    } else {
        $baseQuery .= ", draftTeam=null, draftYear=null, draftRound=null, draftPick=null";
    }
    
    $finalQuery = "$baseQuery WHERE flmid={$player->statId}";
    //print "<p>$finalQuery</p>";
    $result = mysql_query($finalQuery) or die("Unable to update [{$player->statId}] - ".mysql_error());
    $numRows = mysql_affected_rows();
    
    $idQuery = "SELECT playerid FROM newplayers WHERE flmid={$player->statId}";
    $result2 = mysql_query($idQuery) or die("Unable to get ral id [{$player->statId}] - ".mysql_error());
    $resultArray = mysql_fetch_array($result2);
    $player->id = $resultArray[0];
    
    print "Updating {$player->firstName} {$player->lastName}\n";
    return TRUE;
}


/**
 * Determine if the team this player is on is the team they are on in the database.
 * If not make it so.
 * @param Player $player
 */
function updateRoster(Player $player) {
    $query = "SELECT * FROM nflrosters WHERE playerid = {$player->id} AND dateoff is null";
    $result = mysql_query($query) or die ("Unable to get roster for [{$player->id}] - ".mysql_error());
    $resultArray = mysql_fetch_array($result);
    
    
    /*
     * Cases:
     * 	1	Current Team Empty		DB team empty						Do Nothing
     * 	2	Current Team Empty		DB team not empty					End DB entry
     * 	3	Current Team Not Empty	DB team empty						Start new DB entry
     * 	4	Current team not empty	DB team not empty	Equal			Do Nothing
     * 	5	Current Team not empty	DB team not empty	Not Equal		End DB Entry, start New DB entry
     * 
     */
   
    if ($player->team != '') {
        $currentTeamEmpty = false;
    } else {
        $currentTeamEmpty = true;
    }
    
    if ($resultArray) {
        $dbTeamEmpty = false;
    } else {
        $dbTeamEmpty = true;
    }
    
    switch($currentTeamEmpty) {
        case true:
            switch($dbTeamEmpty) {
                case true:
                    // Case 1
                    // No Op
                    break;
                case false:
                    // Case 2
                    endDBRosterEntry($player->id);
                    break;
            }
            break;
        case false:
            switch($dbTeamEmpty) {
                case true:
                    // Case 3
                    startDBRosterEntry($player->id, $player->team);
                    break;
                case false:
                    if ($player->team == $resultArray["nflteamid"]) {
	                    // Case 4
                        // No Op
                    } else {
	                    // Case 5
                        endDBRosterEntry($player->id);
                        startDBRosterEntry($player->id, $player->team);
                    }
                    break;
            }
            break;
    }
    
    
    // If nothing in array then insert
    // If something and something matches, no change
    // If something and something is different, then change
}


function endDBRosterEntry($playerid) {
    $query = "UPDATE nflrosters SET dateoff=now() WHERE dateoff is null AND playerid=$playerid";
    mysql_query($query) or die("Unable to end query for [$playerid] - ".mysql_error());
}

function startDBRosterEntry($playerid, $team) {
    $query = "INSERT INTO nflrosters (playerid, nflteamid, dateon) VALUES ($playerid, '$team', now())";
    mysql_query($query) or die("Unable to start query for [$playerid] - ".mysql_error());
}


function getPlayerByStatId($statId) {
    static $query = "SELECT * FROM newplayers WHERE flmid=%d";    
    
    $result = mysql_query(sprintf($query, $statId)) or die("Error doing select on [$statId] - ".mysql_error());
    $resultArray = mysql_fetch_array($result);
    $player = Player::buildFromDatabase($resultArray);
    return $player;    
    /*
    print "<pre>";
    print_r($resultArray);
    print_r($player);
    print "</pre>";
	*/
} 


function checkForStatId($statId) {
    static $query = "SELECT count(*) FROM newplayers WHERE flmid=%d";
    
    $result = mysql_query(sprintf($query, $statId)) or die("Error doing count on [$statId] - ".mysql_error());
    $num = mysql_fetch_array($result);
    return $num[0]==1 ? true : false;
}


function getLastTimestamp() {
    $timeSql = "SELECT value FROM config WHERE `key`='player.update.timestamp'";
    $result = mysql_query($timeSql) or die("Unable to get latest timestamp: ".mysql_error());
    $numReturn = mysql_num_rows($result);
    if ($numReturn == 0) {
        $setSql = sprintf("INSERT INTO config (`key`, value) VALUES ('player.update.timestamp', '%d')", 0);
        mysql_query($setSql) or die("Unable to insert: ".mysql_error());
       $timestamp = 0; 
    } else {
        $config = mysql_fetch_assoc($result);
        $timestamp = intval($config["value"]);
    }
    return $timestamp;
}

function updateTimestamp($timestamp) {
    $query = "UPDATE config SET value='$timestamp' WHERE `key`='player.update.timestamp'";
    mysql_query($query) or die("Unable to update timestamp: ".mysql_error());
}