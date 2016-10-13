<?
require_once "utils/start.php";

// Query to get current rosters
$sql = <<<EOD

SELECT p.pos, p.playerid, CONCAT(p.firstName, ' ', p.lastName) as 'playername', t.name as 'teamname', t.abbrev, t.teamid, r.dateon
FROM newplayers p
JOIN roster r ON r.playerid=p.playerid and r.dateoff is null
JOIN teamnames t ON r.teamid=t.teamid
WHERE t.season=$currentSeason
ORDER BY t.name, p.pos, p.lastname

EOD;

$results = mysql_query($sql) or die("Error: ".mysql_error());
$team = "";
$first = true;

$result_array = array();
// For each item in the Query
while($row = mysql_fetch_assoc($results)) {
    // If it's a new team add the team array
    if ($row["teamname"] != $team) {
        $first = false;
        $team = $row["teamname"]; 
	$teamArray = array( 'name' => $team, 'id' => $row['teamid'], 'abbrev' => $row['abbrev'], 'players' => array());
	$result_array[$team] = $teamArray;
	//array_push($result_array, $teamArray);
    }

    // Add the player
    $name = trim($row["playername"]);
    $aPlayer = array('name' => $name, 'pos' => $row['pos'], 'id' => $row['playerid']);
    array_push($result_array[$team]['players'], $aPlayer);
}

header("Content-type: text/json");
print json_encode($result_array);
?>
