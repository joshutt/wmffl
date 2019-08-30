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
            $query .= "LastName='" . mysqli_real_escape_string($conn, $name[0]) . "', ";
        if (count($name) > 1) {
            $query .= "FirstName='" . mysqli_real_escape_string($conn, $name[1]) . "', ";
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
        $result = mysqli_query($conn, $query, $conn);
        //print "**".$result."**\n";
        print $query;
        print " - " . mysqli_affected_rows($conn) . " - UPDATED\n";
    } else {
        $query = "No Action";
    }
}


function insertPlayer(&$conn, $player) {
    $query = "INSERT INTO players (LastName, FirstName, NFLTeam, Position, Status, StatID) VALUES ";
    
    // Get Name in correct format
    $fullname = correctCase($player->getName());
    $name = split(",", $fullname);
    $value = "('" . mysqli_real_escape_string($conn, $name[0]) . "',";
    if (count($name) > 1) {
        $value .= "'" . mysqli_real_escape_string($conn, $name[1]) . "',";
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
    $result = mysqli_query($conn, $query . $value, $conn) or die("I'm dead: " . mysqli_error($conn) . "\n");
    if (mysqli_errno($conn) != 0) {
        print mysqli_errno($conn) . ": " . mysqli_error($conn) . "\n";
        die("Oh the humanity\n");
    }

    
    print $value;
    print " - " . mysqli_affected_rows($conn) . "- ADDED\n";
}


function updateDatabase ($playerList) {

	global $database, $db_host, $db_user, $db_pass;
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $database) or die("Can't connect\n");


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
        $check = mysqli_query($conn, $checkQuery . $player->getID());
        $numRows = mysqli_num_rows($check);
        if ($numRows) {
            $checkPlayer = mysqli_fetch_object($check);
            updatePlayer($conn, $player, $checkPlayer);
        } else {
            insertPlayer($conn, $player);
        }
    }

    mysqli_close($conn);
}

?>
