<?
require_once dirname(__FILE__)."/../base.php";

function catchBadFunction($errno, $errstr, $errfile, $errline, $vars) {
    error_log("$errstr in $errfile on line $errline");
    if ($errno == FATAL || $errno == ERROR) {
        exit(1);
    }
//    print_r ($vars);
    print "Problem with player: ".$vars["player"]["playerid"];
    print " - Pos: ".$vars["player"]["position"]."\n";
    $vars["pts"] = 0;
}

//require_once "/home/joshutt/football/utils/start.php";
//include "/home/joshutt/football/base/scoring.php";
include "base/scoring.php";

$week = $currentWeek - 1;
//$week = $currentWeek;
print $week;
print "\n";

$sql = "select p.playerid, p.pos, s.season, s.* ";
$sql .= "from newplayers p ";
$sql .= "join stats s on s.statid=p.flmid ";
$sql .= "left join playerscores ps on ps.playerid=p.playerid and ps.season=s.season and ps.week=s.week ";
$sql .= "where s.season=$currentSeason and s.week=$week";
$sql .= " and ps.playerid is null ";

$bigquery = "insert into playerscores (playerid, season, week, pts) ";
$bigquery .= "values ";

print $sql;
print "\n";
$results = mysqli_query($conn, $sql);
$first = 1;
set_error_handler("catchBadFunction");
while ($player = mysqli_fetch_array($results)) {
    $funcName = "score".$player["pos"];
    if ($player["pos"] == "") {
        $pts = 0;
    } else {
        $pts = call_user_func($funcName, $player);
    }

    if ($first != 1) {
        $bigquery .= ", ";
    }
    $first = 0;
    $bigquery .= "(".$player["playerid"].", ".$player["season"]. ", ";
    $bigquery .= $player["week"].", ".$pts.") ";

}
restore_error_handler();
//print $bigquery; 
mysqli_query($conn, $bigquery);


$querySQL = <<<EOD
    SELECT p.playerid
    FROM newplayers p
    JOIN revisedactivations a ON a.playerid=p.playerid
    WHERE a.season=$currentSeason and a.week=$week
EOD;

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
