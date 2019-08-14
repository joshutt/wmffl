<?
require_once "start.php";

function getTeamList($season) {
    global $conn;
    $sql = "SELECT name, teamid, abbrev FROM teamnames WHERE season=$season ORDER BY name ASC";
    $season = intval($season);
    $results = mysqli_query($conn, $sql) or die("Unable to run query: " . mysqli_error($conn));
    $teamArray = array();
    while ($row = mysqli_fetch_array($results)) {
	$team = array("name" => $row["name"], "id" => $row["teamid"], "abbrev" => $row["abbrev"]);
        array_push($teamArray, $team);
    }
    return $teamArray;
}

function getPosList() {
    return array("HC", "QB", "RB", "WR", "TE", "K", "OL", "DL", "LB", "DB");
}
