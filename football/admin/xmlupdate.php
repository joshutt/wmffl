<?
/*
$conn = mysql_connect('localhost', 'joshutt_misc', 'swwrbo');
mysql_select_db('joshutt_misc');
*/

print "start<br/>";
require_once "utils/start.php";


$request_url = "http://football.myfantasyleague.com/2008/export?TYPE=players&L=&W=";
$xml = simplexml_load_file($request_url) or die("Feed not loading");

print "loaded XML<br/>";

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


// If timestamp indicates a change
// If player doesn't exist, then insert
// select current roster spot
    // if team and pos == do nothing
    // else put update time as end and create new record

$timeSql = "SELECT value FROM config WHERE `key`='player.update.timestamp'";
echo "$timeSql<br/>";
$result = mysql_query($timeSql) or die("Unable to select: ".mysql_error());
$numReturn = mysql_num_rows($result);
if ($numReturn == 0) {
    $setSql = sprintf("INSERT INTO config (`key`, value) VALUES ('player.update.timestamp', '%d')", $timeStamp);
    mysql_query($setSql) or die("Unable to insert: ".mysql_error());

} else {
    $config = mysql_fetch_assoc($result);
    $thisStamp = intval($config["value"]);
    if ($thisStamp < $timeStamp) {
        $setSql = sprintf("UPDATE config SET value='%d' WHERE `key`='player.update.timestamp'", $timeStamp);
        mysql_query($setSql) or die("Unable to update: ".mysql_error());
    } else {
        echo "Here<br/>";
        return;
    }
}




$selectSQL = <<<EOD

SELECT playerid, flmid, lastname, firstname, pos, team
FROM newplayers
WHERE flmid = %d

EOD;


$insertSQL = <<<EOD

INSERT INTO newplayers
(flmid, lastname, firstname, pos, team)
VALUES
(%d, '%s', '%s', '%s', '%s')

EOD;


$updateSQL = <<<EOD

UPDATE newplayers
SET lastname='%s', firstname='%s', pos='%s', team='%s'
WHERE flmid=%d

EOD;


// Save or update the players
foreach ($playerList as &$player) {
    $query = sprintf($selectSQL, $player["flmid"]);
    $result = mysql_query($query);

    $numReturn = mysql_num_rows($result);
    if ($numReturn == 0) {
        //Insert Here
        $query2 = sprintf($insertSQL, $player["flmid"], $player["lastName"], $player["firstName"], $player["pos"], $player["team"]);
        mysql_query($query2);
        $player["playerid"] = mysql_insert_id();
    } else {
        // Update if necessary
        $row = mysql_fetch_assoc($result);
        if ($row["lastname"] != $player["lastName"]  || $row["firstname"] != $player["firstName"]
            || $row["pos"] != $player["pos"] || $row["team"] != $player["team"])    {

            $query2 = sprintf($updateSQL, $player["lastName"], $player["firstName"], $player["pos"], $player["team"], $player["flmid"]);
            mysql_query($query2);

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

foreach ($playerList as $player) {
    $query = sprintf($currentSql, $player["playerid"]);
    $result = mysql_query($query) or die("Unable to get current roster: ".mysql_error());
    $numRows = mysql_num_rows($result);

    if ($player["team"] != "") {
        if ($numRows > 0) {
            $row = mysql_fetch_assoc($result);
            if ($row["team"] != $player["team"]) {
                // matches, do nothing
            } else {
                // Doesn't match, close old create new
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
            // Create new
            if ($insertBase != "") {
                $insertBase .= ", ";
            }
            $insertBase .= "({$player["playerid"]}, '{$player["team"]}', now(), null)";
        }
    } else {
        if ($numRows > 0) {
            // End current
            if ($closeBase != "") {
                $closeBase .= ", ";
            }
            $closeBase .= $player["playerid"];
        } else {
            // Do nothing
        }
    }
}


if ($insertBase != "") {
    $insertQuery = "INSERT INTO nflrosters (playerid, nflteamid, dateon, dateoff) VALUES $insertBase";
    mysql_query($insertQuery) or die ("Unable to insert: ".mysql_error());
    $affectedRows = mysql_affected_rows();
    print "Inserted $affectedRows rows<br/>";
}

if ($closeBase != "") {
    $closeQuery = "UPDATE nflrosters SET dateoff=now() WHERE dateoff is null AND playerid in ($closeBase)";
    mysql_query($closeQuery) or die ("Unable to update: ".mysql_error());
    $affectedRows = mysql_affected_rows();
    print "Updated $affectedRows rows<br/>";
}

?>
