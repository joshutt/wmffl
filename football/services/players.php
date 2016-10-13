<?
require_once "utils/start.php";

// Get extra where
$where = "";
if (isset($_REQUEST["pos"])) {
    $where .= "and p.pos='${_REQUEST["pos"]}' ";
}
if (isset($_REQUEST["nflteam"])) {
    $where .= "and p.team='${_REQUEST["nflteam"]}' ";
}

// Query to get current rosters
$sql = <<<EOD

SELECT p.playerid, p.lastname, p.firstname, p.pos, p.team, p.number, p.height, p.weight, p.dob
FROM newplayers p
JOIN nflrosters r on p.playerid=r.playerid and r.dateoff is null
WHERE p.active=1 and p.usePos=1 $where
ORDER BY p.playerid

EOD;

$results = mysql_query($sql) or die("Error: ".mysql_error());
$team = "";
$first = true;

$result_array = array();
// For each item in the Query
while($row = mysql_fetch_assoc($results)) {
    array_push($result_array, $row);
}

header("Content-type: text/json");
print json_encode($result_array);
?>
