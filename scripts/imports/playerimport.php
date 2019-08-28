<?
$conn = mysqli_connect('localhost', 'joshutt_footbal', 'wmaccess', 'joshutt_oldwmffl');

$currentSeason=2011;
#$currentSeason=2009;

$request_url = "http://football.myfantasyleague.com/$currentSeason/export?TYPE=players";
$xml = simplexml_load_file($request_url) or die("Feed not loading");



/*
echo "<pre>";
var_dump($xml);
echo "</pre>";
*/


function determineTeam($teamId) {
    switch ($teamId) {
        case 'BUF':  case 'IND':  case 'MIA':  case 'NYJ':  case 'CIN':
        case 'CLE':  case 'TEN':  case 'JAC':  case 'PIT':  case 'DEN':
        case 'OAK':  case 'SEA':  case 'DAL':  case 'NYG':  case 'PHI':
        case 'ARI':  case 'WAS':  case 'CHI':  case 'DET':  case 'MIN':
        case 'ATL':  case 'CAR':  case 'BAL':  case 'HOU':
            $teamVal = $teamId;
            break;
        case 'NEP': $teamVal = 'NE'; break;
        case 'KCC': $teamVal = 'KC'; break;
        case 'SDC': $teamVal = 'SD'; break;
        case 'GBP': $teamVal = 'GB'; break;
        case 'TBB': $teamVal = 'TB'; break;
        case 'NOS': $teamVal = 'NO'; break;
        case 'SFO': $teamVal = 'SF'; break;
        case 'RAM': $teamVal = 'LA'; break;
        default:    $teamVal = '';
    }
    return $teamVal;
}


$timeStamp = $xml['timestamp'];
echo date('F j, Y  g:i a', intval($timeStamp))."<br/>";

$playerList = array();

//echo "<table>";
//echo "<tr><th>ID</th><th>Last Name</th><th>First Name</th><th>Pos</th><th>Team</th><tr>";
foreach ($xml->player as $player) {
    //print_r($player);
    $playerId = array();

    switch ($player['position']) {
        case 'Off': $position = 'OL';  break;
        case 'QB':
        case 'RB':
        case 'WR':
        case 'TE':
        case 'LB':
                    $position = $player['position'];
                    break;
        case 'PK':  $position = 'K'; break;
        case 'DB':
        case 'CB':
        case 'S':
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

    if ($position != '') {
        $name = split(",",$player['name']);
        $team = determineTeam($player['team']);

        /*
        echo "<tr><td>{$player['id']}</td><td>{$name[0]}</td>";
        echo "<td>{$name[1]}</td><td>$position</td>";
        echo "<td>$team</td></tr>";
        */
    } else {
        continue;
    }

    $playerId["pos"] = $position;
    $playerId["lastName"] = $name[0];
    $playerId["firstName"] = $name[1];
    $playerId["team"] = $team;
    $playerId["flmid"] = $player['id'];

    array_push($playerList, $playerId);
}
//echo "</table>";
//exit();

//print_r($playerList);


// If timestamp indicates a change
// If player doesn't exist, then insert
// select current roster spot
    // if team and pos == do nothing
    // else put update time as end and create new record

$timeSql = "SELECT value FROM config WHERE `key`='player.update.timestamp'";
echo "$timeSql<br/>";
$result = mysqli_query($conn, $timeSql) or die("Unable to select: " . mysqli_error($conn));
$numReturn = mysqli_num_rows($result);
if ($numReturn == 0) {
    $setSql = sprintf("INSERT INTO config (`key`, value) VALUES ('player.update.timestamp', '%d')", $timeStamp);
    mysqli_query($conn, $setSql) or die("Unable to insert: " . mysqli_error($conn));

} else {
    $config = mysqli_fetch_assoc($result);
    $thisStamp = intval($config["value"]);
    if ($thisStamp < $timeStamp) {
        $setSql = sprintf("UPDATE config SET value='%d' WHERE `key`='player.update.timestamp'", $timeStamp);
        mysqli_query($conn, $setSql) or die("Unable to update: " . mysqli_error($conn));
    } else {
        echo "Here<br/>";
        return;
    }
}




$selectSQL = <<<EOD

SELECT playerid, flmid, lastname, firstname, pos, team, active
FROM newplayers
WHERE flmid = %d

EOD;


$insertSQL = <<<EOD

INSERT INTO newplayers
(flmid, lastname, firstname, pos, team, active)
VALUES
(%d, '%s', '%s', '%s', '%s', 1)

EOD;


$updateSQL = <<<EOD

UPDATE newplayers
SET lastname='%s', firstname='%s', pos='%s', team='%s', active=1
WHERE flmid=%d

EOD;


// Save or update the players
foreach ($playerList as &$player) {
    $query = sprintf($selectSQL, $player["flmid"]);
    $result = mysqli_query($conn, $query);

    $numReturn = mysqli_num_rows($result);
    if ($numReturn == 0) {
        //Insert Here
        $query2 = sprintf($insertSQL, $player["flmid"], mysqli_real_escape_string($conn, $player["lastName"]), mysqli_real_escape_string($conn, $player["firstName"]), $player["pos"], $player["team"]);
        mysqli_query($conn, $query2) or die("Ug: " . mysqli_error($conn));
        $player["playerid"] = mysqli_insert_id($conn);
        #print $query2;
    } else {
        // Update if necessary
        $row = mysqli_fetch_assoc($result);
        if ($row["lastname"] != $player["lastName"]  || $row["firstname"] != $player["firstName"] || $row["active"] != 1
            || $row["pos"] != $player["pos"] || $row["team"] != $player["team"])    {

            $query2 = sprintf($updateSQL, mysqli_real_escape_string($conn, $player["lastName"]), mysqli_real_escape_string($conn, $player["firstName"]), $player["pos"], $player["team"], $player["flmid"]);
            mysqli_query($conn, $query2) or die("Ug: " . mysqli_error($conn));
//print $query2;

        }
        $player["playerid"] = $row["playerid"];
    }

    //print_r($player);
}


/*
On team
    - Has current
        Matches     =   do nothing
        No Match    =   end current, create new
    - No current
        Create new

Not on team
    - Has current
        End current

    - No current
        Do nothing

*/


$currentSql = "SELECT * FROM nflrosters WHERE dateoff is null and playerid=%d";
$insertBase = "";
$closeBase = "";

//print_r($playerList);
//print "--".sizeof($playerList)."--";
foreach ($playerList as &$player) {
//print "--".$playerList[1960]["playerid"]."--";
    $query = sprintf($currentSql, $player["playerid"]);
    $result = mysqli_query($conn, $query) or die("Unable to get current roster: " . mysqli_error($conn));
    $numRows = mysqli_num_rows($result);
    //print "{$player["playerid"]} - {$player["firstName"]} - {$player["lastName"]} - {$player["team"]} - ";

    if ($player["team"] != "") {
        if ($numRows > 0) {
            $row = mysqli_fetch_assoc($result);
            if ($row["nflteamid"] == $player["team"]) {
                print "Match";
                // matches, do nothing
            } else {
                // Doesn't match, close old create new
                print "No Match";
                if ($closeBase != "") {
                    $closeBase .= ", ";
                }
                $closeBase .= $player["playerid"];
                if ($insertBase != "") {
                    $insertBase .= ", ";
                }
                $insertBase .= "({$player["playerid"]}, '{$player["team"]}', now(), null)";
            }

        } else {
            print "New";
            // Create new
            if ($insertBase != "") {
                $insertBase .= ", ";
            }
            $insertBase .= "({$player["playerid"]}, '{$player["team"]}', now(), null)";
        }
    } else {
        if ($numRows > 0) {
            print "End Current";
            // End current
            if ($closeBase != "") {
                $closeBase .= ", ";
            }
            $closeBase .= $player["playerid"];
        } else {
            print "Nothing";
            // Do nothing
        }
    }
    print "<br/>";
}

//print_r($player);
//exit();

if ($closeBase != "") {
    $closeQuery = "UPDATE nflrosters SET dateoff=now() WHERE dateoff is null AND playerid in ($closeBase)";
    mysqli_query($conn, $closeQuery) or die ("Unable to update: " . mysqli_error($conn));
    $affectedRows = mysqli_affected_rows($conn);
    print "Updated $affectedRows rows<br/>";
}

if ($insertBase != "") {
    $insertQuery = "INSERT INTO nflrosters (playerid, nflteamid, dateon, dateoff) VALUES $insertBase";
    mysqli_query($conn, $insertQuery) or die ("Unable to insert: " . mysqli_error($conn));
    $affectedRows = mysqli_affected_rows($conn);
    print "Inserted $affectedRows rows<br/>";
}


// Query to update all the overrides
$overrideQuery = <<<EOD

UPDATE newplayers p JOIN roster r ON p.playerid=r.playerid AND r.dateoff is null
JOIN playeroverride o ON o.playerid=p.playerid AND r.teamid=o.teamid
SET p.pos=o.pos
WHERE o.season=$currentSeason

EOD;

mysqli_query($conn, $overrideQuery) or die ("Unable to override: " . mysqli_error($conn));
$affectedRows = mysqli_affected_rows($conn);
print "Overrode $affectedRows players<br/>";

?>
