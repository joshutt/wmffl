<?
require_once "$DOCUMENT_ROOT/base/conn.php";

$sql = "SELECT t.name, p.firstname, p.lastname, p.position, ps.week, ps.pts ";
$sql .= "FROM players p, roster r, team t, playerscores ps ";
$sql .= "WHERE p.playerid=r.playerid and r.dateoff is null ";
$sql .= "and r.teamid=t.teamid and p.playerid=ps.playerid ";
$sql .= "and ps.season=2003 ";
$sql .= "ORDER BY t.name, ps.week, p.position ";

$results = mysql_query($sql);

$totalArray = array();
$currentTeam = "";
$currentPos = "";
$currentWeek = 0;
$maxScore = -99;
$secScore = -99;
$actScore = 0;

while ($obj = mysql_fetch_array($results)) {
    if ($obj["name"] != $currentTeam) {
       if ($currentTeam != "" && $currentWeek!=0 && $currentPos != "") {
       $totalArray[$currentTeam][$currentWeek][$currentPos] = $actScore;}
       $currentTeam = $obj["name"];
       $totalArray[$currentTeam] = array();
       $currentPos = "";
       $currentWeek = 0;
       $maxScore = -99;
       $secScore = -99;
    }
    if ($obj["week"] != $currentWeek) {
        if ($currentWeek !=0 && $currentPos != "") {
        $totalArray[$currentTeam][$currentWeek][$currentPos] = $actScore;}
        $currentWeek = $obj["week"];
        $totalArray[$currentTeam][$currentWeek] = array();
        $currentPos = "";
        $maxScore = -99;
        $secScore = -99;
    }
    if ($obj["position"] != $currentPos) {
        if ($currentPos != "") {
        $totalArray[$currentTeam][$currentWeek][$currentPos] = $actScore;}
        $currentPos = $obj["position"];
        $maxScore = -99;
        $secScore = -99;
    }
    if ($obj["pts"] > $maxScore) {
        $maxScore = $obj["pts"];
    } else if ($obj["pts"] > $secScore) {
        $secScore = $obj["pts"];
    }
    if ($currentPos == "RB" || $currentPos =="WR" || $currentPos =="DL" ||
        $currentPos == "LB" || $currentPos == "DB") {
        $actScore = $maxScore + $secScore;
    } else {
        $actScore = $maxScore;
    }
}


print "<TABLE>";
foreach($totalArray as $team=>$pattern) {
    print "<TR><TH>$team</TH></TR>";
    foreach ($pattern as $week=>$scores) {
        print "<TR><TH>$week</TH></TR>";
        foreach ($scores as $pos=>$pts) {
            print "<TR><TD>$pos</TD><TD>$pts</TD></TR>";
        }
    }
}
print "</TABLE>";
?>
