<?
require_once "$DOCUMENT_ROOT/base/conn.php";

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
