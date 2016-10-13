<?
require_once "$DOCUMENT_ROOT/base/conn.php";
include "$DOCUMENT_ROOT/base/scoring.php";

//$playerid = $HTTP_GET["playerid"];
//$season = $HTTP_GET["season"];
//$week = $HTTP_GET["week"];
$season=2003;
$week=4;

$sql = "SELECT p.firstname, p.lastname, p.position, p.nflteam, s.* ";
$sql .= "FROM stats s, players p ";
$sql .= "WHERE s.statid=p.statid AND p.playerid=$playerid ";
$sql .= "AND s.season=$season and s.week=$week ";
//print $sql;

$results = mysql_query($sql) or die("Uggg: ".mysql_error());
//print "**$results**";
while ($player = mysql_fetch_array($results)) {
    print $player["firstname"]." ".$player["lastname"]."<BR>";
    print $player["position"]." - ".$player["nflteam"]."<BR>";
    $scorePos = $player["position"];
    if ($scorePos == 'RB' || $scorePos == 'WR') {
        $scorePos = "Offense";
    } elseif ($scorePos == 'LB' || $scorePos == 'DL' || $scorePos == 'DB') {
        $scorePos = "Defense";
    }
    $funcName = 'score'.$scorePos;
    $pts = call_user_func($funcName, $player);

    switch ($scorePos) {
        case 'QB' :
            print $player["yards"]." yards<BR>";
            print $player["tds"]." TDs<BR>";
            print $player["2pt"]." 2-point conversions<BR>";
            print $player["intthrow"]." INTs<BR>";
            print $player["fum"]." fumbles<BR>";
            break;
        case 'Offense' :
            print $player["rec"]." Receptions<BR>";
            print $player["yards"]." yards<BR>";
            print $player["tds"]." TDs<BR>";
            print $player["2pt"]." 2-point conversions<BR>";
            print $player["specTD"]." Special Teams TDs<BR>";
            print $player["fum"]." fumbles<BR>";
            break;
    }
    print "<B>".$pts." Points</B><BR>";
    
}

?>
