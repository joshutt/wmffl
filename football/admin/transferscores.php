<?

function catchBadFunction($errno, $errstr, $errfile, $errline, $vars)
{
    error_log("$errstr in $errfile on line $errline");
    if ($errno == FATAL || $errno == ERROR) {
        exit(1);
    }
//    print_r ($vars);
    print "Problem with player: " . $vars["player"]["playerid"];
    print " - Pos: " . $vars["player"]["position"] . "\n";
    $vars["pts"] = 0;
}

require_once "/home/wmffl/public_html/util/start.php";
#require_once "/home/wmffl/public_html/base/conn.php";
#include "/home/wmffl/public_html/base/useful.php";
include "/home/wmffl/public_html/base/scoring.php";

//$week = $currentWeek - 1;
$week = $currentWeek;

$sql = "select p.playerid, p.position, s.season, s.* from players p, stats s ";
$sql .= "where s.statid=p.statid and s.season=$currentSeason and week=$week";

$bigquery = "insert into playerscores (playerid, season, week, pts) ";
$bigquery .= "values ";

$results = mysqli_query($conn, $sql);
$first = 1;
set_error_handler("catchBadFunction");
while ($player = mysqli_fetch_array($results)) {
    $funcName = "score" . $player["position"];
    $pts = call_user_func($funcName, $player);

    if ($first != 1) {
        $bigquery .= ", ";
    }
    $first = 0;
    $bigquery .= "(" . $player["playerid"] . ", " . $player["season"] . ", ";
    $bigquery .= $player["week"] . ", " . $pts . ") ";

}
restore_error_handler();
//print $bigquery; 
mysqli_query($conn, $bigquery);


$querySQL = "SELECT p.playerid FROM players p, activations a ";
$querySQL .= "WHERE a.season=$currentSeason AND a.week=$week AND p.playerid in ";
$querySQL .= "(a.HC, a.QB, a.RB1, a.RB2, a.WR1, a.WR2, a.TE, a.K, a.OL, ";
$querySQL .= "a.DL1, a.DL2, a.LB1, a.LB2, a.DB1, a.DB2) ";

$sql = "UPDATE playerscores SET active=pts WHERE season=$currentSeason AND week=$week ";
$sql .= "AND playerid in (";

$results = mysqli_query($conn, $querySQL);
$first = 1;
while (list($playerid) = mysqli_fetch_row($results)) {
    if ($first != 1) {
        $sql .= ", ";
    }
    $first = 0;
    $sql .= $playerid;
}
$sql .= ")";
mysqli_query($conn, $sql);

print "Successfully Transfered scores\n";

?>
