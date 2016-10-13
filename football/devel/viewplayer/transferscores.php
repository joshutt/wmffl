<?

require_once "$DOCUMENT_ROOT/base/conn.php";
include "$DOCUMENT_ROOT/base/scoring.php";

$sql = "select p.playerid, p.position, s.season, s.* from players p, stats s ";
$sql .= "where s.statid=p.statid and s.season=2003 and week=1";

$bigquery = "insert into playerscores (playerid, season, week, pts) ";
$bigquery .= "values ";

$results = mysql_query($sql);
$first = 1;
while($player = mysql_fetch_array($results)) {
    $funcName = "score".$player["position"];
    $pts = call_user_func($funcName, $player);

    if ($first != 1) {
        $bigquery .= ", ";
    }
    $first = 0;
    $bigquery .= "(".$player["playerid"].", ".$player["season"]. ", ";
    $bigquery .= $player["week"].", ".$pts.") ";

}
//print $bigquery; 
mysql_query($bigquery);


$querySQL = "SELECT p.playerid FROM players p, activations a ";
$querySQL .= "WHERE a.season=2003 AND a.week=1 AND p.playerid in ";
$querySQL .= "(a.HC, a.QB, a.RB1, a.RB2, a.WR1, a.WR2, a.TE, a.K, a.OL, ";
$querySQL .= "a.DL1, a.DL2, a.LB1, a.LB2, a.DB1, a.DB2) ";

$sql = "UPDATE playerscores SET active=pts WHERE season=2003 AND week=1 ";
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

?>
