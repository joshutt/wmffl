<?

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

require_once "/home/joshutt/football/utils/start.php";
include "/home/joshutt/football/base/scoring.php";

$week = $currentWeek - 1;
//$week = $currentWeek;

$sql = "select p.playerid, p.pos, s.season, s.* from newplayers p, stats s ";
$sql .= "left join playerscores ps on ps.playerid=p.playerid and ps.season=s.season and ps.week=s.week ";
$sql .= "where s.statid=p.flmid and s.season=$currentSeason and s.week=$week";
$sql .= " and ps.playerid is null ";

$bigquery = "insert into playerscores (playerid, season, week, pts) ";
$bigquery .= "values ";

$results = mysql_query($sql);
$first = 1;
set_error_handler("catchBadFunction");
while($player = mysql_fetch_array($results)) {
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
mysql_query($bigquery);


$querySQL = <<<EOD
    SELECT p.playerid
    FROM newplayers p
    JOIN revisedactivations a ON a.playerid=p.playerid
    WHERE a.season=$currentSeason and a.week=$week
EOD;

$sql = "UPDATE playerscores SET active=pts WHERE season=$currentSeason AND week=$week ";
$sql .= "AND playerid in (";

$results = mysql_query($querySQL);
$first = 1;
while (list($playerid) = mysql_fetch_row($results)) {
    if ($first != 1) {
        $sql .= ", ";
    }
    $first = 0;
    $sql .= $playerid;
}
$sql .= ")";
mysql_query($sql);

print "Successfully Transfered scores\n";

?>
