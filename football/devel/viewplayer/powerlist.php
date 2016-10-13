<?
require_once "$DOCUMENT_ROOT/utils/start.php";
#require_once $DOCUMENT_ROOT."/base/conn.php";
#require_once $DOCUMENT_ROOT."/base/useful.php";
#$currentSeason=2004;

$sql = "select w.week, t.name, p.firstname, p.lastname, p.position, ps.pts, ";
$sql .= "ps.active ";
$sql .= "from roster r, weekmap w, players p, team t, playerscores ps ";
$sql .= "where w.season=$currentSeason and r.dateon <= w.activationdue ";
$sql .= "and w.week<=14 ";
$sql .= "and (r.dateoff > w.activationdue or r.dateoff is null) ";
$sql .= "and r.playerid=p.playerid and r.teamid=t.teamid ";
$sql .= "and ps.week=w.week and ps.season=w.season and ps.playerid=p.playerid ";
$sql .= "order by w.week, t.name, p.position, ps.pts desc";

$results = mysql_query($sql);
$potPts = array();
$actPts = array();

$curTeam = "";
$curPos = "";
while ($row = mysql_fetch_array($results)) {
    $week = $row["week"];
    $teamName = $row["name"];
    if ($curTeam != $teamName) {
        $curPos = "";
        $curTeam = $teamName;
        $count = 0;
        if (!array_key_exists($teamName, $potPts)) {
            $potPts[$teamName] = array();
            $actPts[$teamName] = array();
        }
        $ppt = 0;
        $apt = 0;
    }

    $plp = $row["pts"];
    $pla = $row["active"];
    $apt += $pla;
    if ($curPos != $row["position"]) {
        $ppt += $plp;
        $curPos = $row["position"];
        $count = 0;
    }
    if (($curPos == 'RB' || $curPos == 'WR' || $curPos == 'DL' ||
        $curPos == 'LB' || $curPos == 'DB') && $count==1) {
            $ppt += $plp;
    }
    $count++;

    $potPts[$teamName][$week] = $ppt;
    $actPts[$teamName][$week] = $apt;
}

foreach($potPts as $team=>$teamArray) {
    $weight = 0.0;
    $totPot = 0;
    $totAct = 0;
    $weighPot = 0.0;
    $weighAct = 0.0;
    $powArray = array();
    $flatPower = array();
    foreach($teamArray as $week=>$value) {
        print "Week: $week - Team: $team - ";
        print "Pot: $value - Act: ".$actPts[$team][$week]."<BR>";
        $newWeight = sqrt($week);
        $weight += $newWeight;
        $totPot += $value;
        $totAct += $actPts[$team][$week];
        $weighPot += $newWeight * $value;
        $weighAct += $newWeight * $actPts[$team][$week];
        $powArray[$week] = ($weighPot + 2.0*(float)$weighAct) / (3.0*$weight);
        $flatPower[$week] = ($totPot + 2.0*$totAct) / (3.0*$week);
    }
    $power = ($weighPot + 2.0*(float)$weighAct) / (3.0*$weight);
    $flat = ($totPot + 2.0*$totAct) / (3.0*$week);
    $finalPower = ($power+$flat)/2.0;
    $actPCT = $totAct/$totPot;
    print "Total Potential: $totPot   Total Actual: $totAct <BR>";
    printf ("Power: %6.2f   Activate: %5.3f<BR>", $finalPower, $actPCT);

    foreach($powArray as $week=>$power) {
        printf ("Week: %d   - Power: %6.2f<BR>", $week, ($power+$flatPower[$week])/2);
    }
    print "<BR>";
}
?>
