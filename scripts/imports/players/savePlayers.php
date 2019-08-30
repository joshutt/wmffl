<?php
require_once 'playerHelpers.php';

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
    global $conn;
    $firstName = mysqli_real_escape_string($conn, $player->firstName);
    $lastName = mysqli_real_escape_string($conn, $player->lastName);
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
    $result = mysqli_query($conn, $finalQuery) or die("Unable to insert [{$player->statId}] - " . mysqli_error($conn));
    $numRows = mysqli_affected_rows($conn);
    $newId = mysqli_insert_id($conn);
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
    global $conn;
    $baseQuery = "UPDATE newplayers ";

    $firstName = mysqli_real_escape_string($conn, $player->firstName);
    $lastName = mysqli_real_escape_string($conn, $player->lastName);
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
    $result = mysqli_query($conn, $finalQuery) or die("Unable to update [{$player->statId}] - " . mysqli_error($conn));
    $numRows = mysqli_affected_rows($conn);
    
    $idQuery = "SELECT playerid FROM newplayers WHERE flmid={$player->statId}";
    $result2 = mysqli_query($conn, $idQuery) or die("Unable to get ral id [{$player->statId}] - " . mysqli_error($conn));
    $resultArray = mysqli_fetch_array($result2);
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
    global $conn;
    $query = "SELECT * FROM nflrosters WHERE playerid = {$player->id} AND dateoff is null";
    $result = mysqli_query($conn, $query) or die ("Unable to get roster for [{$player->id}] - " . mysqli_error($conn));
    $resultArray = mysqli_fetch_array($result);
    
    
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
                        //print($player->id);
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
    global $conn;
    $query = "UPDATE nflrosters SET dateoff=now() WHERE dateoff is null AND playerid=$playerid";
    mysqli_query($conn, $query) or die("Unable to end query for [$playerid] - " . mysqli_error($conn));
}

function startDBRosterEntry($playerid, $team) {
    global $conn;
    $query = "INSERT INTO nflrosters (playerid, nflteamid, dateon) VALUES ($playerid, '$team', now())";
    mysqli_query($conn, $query) or die("Unable to start query for [$playerid] - " . mysqli_error($conn));
}


function getPlayerByStatId($statId) {
    global $conn;
    static $query = "SELECT * FROM newplayers WHERE flmid=%d";

    $result = mysqli_query($conn, sprintf($query, $statId)) or die("Error doing select on [$statId] - " . mysqli_error($conn));
    $resultArray = mysqli_fetch_array($result);
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
    global $conn;
    static $query = "SELECT count(*) FROM newplayers WHERE flmid=%d";

    $result = mysqli_query($conn, sprintf($query, $statId)) or die("Error doing count on [$statId] - " . mysqli_error($conn));
    $num = mysqli_fetch_array($result);
    return $num[0]==1 ? true : false;
}


function getLastTimestamp() {
    global $conn;
    $timeSql = "SELECT value FROM config WHERE `key`='player.update.timestamp'";
    $result = mysqli_query($conn, $timeSql) or die("Unable to get latest timestamp: " . mysqli_error($conn));
    $numReturn = mysqli_num_rows($result);
    if ($numReturn == 0) {
        $setSql = sprintf("INSERT INTO config (`key`, value) VALUES ('player.update.timestamp', '%d')", 0);
        mysqli_query($conn, $setSql) or die("Unable to insert: " . mysqli_error($conn));
       $timestamp = 0; 
    } else {
        $config = mysqli_fetch_assoc($result);
        $timestamp = intval($config["value"]);
    }
    return $timestamp;
}

function updateTimestamp($timestamp) {
    global $conn;
    $query = "UPDATE config SET value='$timestamp' WHERE `key`='player.update.timestamp'";
    mysqli_query($conn, $query) or die("Unable to update timestamp: " . mysqli_error($conn));
}
