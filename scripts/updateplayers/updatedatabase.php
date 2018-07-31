<?
require_once "config.php";

function correctCase($playerName) {
    $fullName = strtolower($playerName);
    $words = str_replace(Array("(", "-", ".", "?", ",",":","[",";","!"), Array("( ", "- ", ". ", "? ", ", ",": ","[ ","; ","! "), $fullName);
    $words = ucwords(strtolower($words));
    $words = str_replace(Array("( ", "- ", ". ", "? ", ", ",": ","[ ","; ","! "), Array("(", "-", ".", "?", ",",":","[",";","!"), $words);
    return $words;
}

function updatePlayer(&$conn, $player, $checkPlayer) {
    $updateCheck =0;
    $query = "UPDATE players SET ";

    // Determine if Name needs to be updated
    $checkedName = $checkPlayer->LastName.",".$checkPlayer->FirstName;
    if ($checkPlayer->FirstName == NULL || $checkPlayer->FirstName == '') {
        return;
    }
    if (strcasecmp($player->getName(), $checkedName) != 0) {
        if (strcasecmp($player->getName(), substr($checkedName, 0, -1)) != 0 ||
            substr($checkedName, -1) != ',') {
        $fullname = correctCase($player->getName());
        $name = split(",", $fullname);
        $query .= "LastName='".mysql_escape_string($name[0])."', ";
        if (count($name) > 1) {
            $query .= "FirstName='".mysql_escape_string($name[1])."', ";
        } else {
            if ($player->getPosition() != 'OL') return;
            $query .= "FirstName='', ";
        }
        $updateCheck = $updateCheck | 1;
        }
    }

    //Determine if team needs to be updated
    $checkedTeam = $checkPlayer->NFLTeam;
    if ($player->getTeam() == 'None') {
        $thisTeam = '';
    } else {
        $thisTeam = $player->getTeam();
    }
    if ($thisTeam != $checkedTeam) {
        $query .= "NFLTeam=";
        if ($thisTeam == '') {
            $query .= "NULL, ";
        } else {
            $query .= "'".$thisTeam."', ";
        }
        $updateCheck = $updateCheck | 2;
    }

    //Determine if position needs to be updated
    if ($player->getPosition() != $checkPlayer->Position) {
        $query .= "Position='".$player->getPosition()."', ";
        $updateCheck = $updateCheck | 4;
    }

    // Determine if status needs to be updated
    if ($player->getYearRetired() != 0) {
        $myStatus = "R";
    } else if ($player->getTeam() == 'None') {
        $myStatus = "N";
    } else {
        $myStatus = "A";
    }
    if ($myStatus != $checkPlayer->Status) {
        $query .= "Status='".$myStatus."', ";
        $updateCheck = $updateCheck | 8;
    }

    if ($updateCheck) {
        $query = substr($query, 0, -2);
        $query .= " WHERE statid=".$checkPlayer->StatID;
        $result = mysql_query($query, $conn);
        //print "**".$result."**\n";
        print $query;
        print " - ".mysql_affected_rows()." - UPDATED\n";
    } else {
        $query = "No Action";
    }
}


function insertPlayer(&$conn, $player) {
    $query = "INSERT INTO players (LastName, FirstName, NFLTeam, Position, Status, StatID) VALUES ";
    
    // Get Name in correct format
    $fullname = correctCase($player->getName());
    $name = split(",", $fullname);
    $value =  "('".mysql_escape_string($name[0])."',";
    if (count($name) > 1) {
        $value .= "'".mysql_escape_string($name[1])."',";
    } else {
        if ($player->getPosition() != 'OL') return;
        $value .= "'',";
    }

    // Get the correct team info
    if ($player->getTeam() == 'None' || $player->getYearRetired() != 0) {
        $value .= "NULL,";
    } else {
        $value .= "'".$player->getTeam()."',";
    }
    
    $value .= "'".$player->getPosition()."',";

    // Get correct status
    if ($player->getYearRetired() != 0) {
        $value .= "'R',";
    } else if ($player->getTeam() == 'None') {
        $value .= "'N',";
    } else {
        $value .= "'A',";
    }
    
    $value .= $player->getID().")";

    print $query.$value."\n";
    $result = mysql_query($query.$value, $conn) or die("I'm dead: ".mysql_error()."\n");
    if (mysql_errno != 0) {
        print mysql_errno().": ".mysql_error()."\n";
        die("Oh the humanity\n");
    }

    
    print $value;
    print " - ".mysql_affected_rows()."- ADDED\n";
}


function updateDatabase ($playerList) {

	global $database, $db_host, $db_user, $db_pass;
    $conn = mysql_connect($db_host, $db_user, $db_pass) or die("Can't connect\n");
    mysql_select_db($database) or die("Can't select database\n");

    //print "OK, I'm doing it now\n";

    $checkQuery = "SELECT * FROM players WHERE statid=";

    // Display output
    foreach ($playerList as $player) {
        if ($player->getPosition() == "*") continue;
        $player->processTransactions();
/*        print $player->getName()." = ";
        if ($player->getYearRetired() != 0) {
            //print "Retired ".$player->getYearRetired()." ".$player->getTeam()."= ";
            print "Retired ".$player->getYearRetired()." = ";
        } else {
            print $player->getTeam()." = ";
        }
        //print $player->getPosition()."\n";
        print $player->getPosition();
*/
        $check = mysql_query($checkQuery.$player->getID());
        $numRows = mysql_num_rows($check);
        if ($numRows) {
            $checkPlayer = mysql_fetch_object($check);
            updatePlayer($conn, $player, $checkPlayer);
        } else {
            insertPlayer($conn, $player);
        }
    }

    mysql_close($conn);
}

?>
