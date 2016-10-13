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

//$week = $currentWeek - 1;
$week = $currentWeek;

$sql = "select p.playerid, p.pos, s.season, s.* from newplayers p, stats s ";
$sql .= "where s.statid=p.flmid and s.season=$currentSeason and week=$week";

$bigquery = "insert into playerscores (playerid, season, week, pts) ";
$bigquery .= "values ";

$results = mysql_query($sql) or die("Dead on select: ".mysql_error());
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
print $bigquery; 
mysql_query($bigquery) or die("Error: ".mysql_error());


$querySql = <<<EOD
    UPDATE playerscores ps
    JOIN newplayers p ON ps.playerid=p.playerid
    JOIN revisedactivations a ON p.playerid=a.playerid AND a.season=ps.season AND a.week=ps.week
    SET ps.active=ps.pts
    WHERE ps.season=$currentSeason AND ps.week=$week
EOD;

$results = mysql_query($querySql) or die("Error: ".mysql_error());

print "Successfully Transfered ".mysql_affected_rows($results)." scores\n";

?>
