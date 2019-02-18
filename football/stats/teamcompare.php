<?
function determineRecord(&$werRec, $werScore, $otherScore) {
    if ($werScore > $otherScore) {
        $werRec["win"]++;
    } else if ($werScore < $otherScore) {
        $werRec["lose"]++;
    } else {
        $werRec["tie"]++;
    }
}

include "base/conn.php";
include "base/useful.php";

$sql = "SELECT t.name, ps.week, p.pos, ps.pts ";
$sql .= "FROM roster r, team t, newplayers p, playerscores ps ";
$sql .= "WHERE r.teamid=t.teamid and r.playerid=p.playerid ";
$sql .= "AND r.dateoff is null and ps.playerid=p.playerid ";
$sql .= "and ps.season=$currentSeason ";
$sql .= "order by t.teamid, p.pos, ps.week, ps.pts DESC ";

$results = mysql_query($sql);

while ($resArry = mysql_fetch_array($results)) {
    $name = $resArry["name"];
    $pos = $resArry["pos"];
    $week = $resArry["week"];
    
    $tot[$name][$pos][$week]["count"]++;
    if ($pos == 'HC' || $pos=='QB' || $pos=='TE' || $pos=='K' || $pos=='OL') {
        if ($tot[$name][$pos][$week]["count"] <= 1) {
            $tot[$name][$pos][$week]["pts"] += $resArry["pts"];
        }
    } else {
        if ($tot[$name][$pos][$week]["count"] <= 2) {
            $tot[$name][$pos][$week]["pts"] += $resArry["pts"];
        }
    }
}

$numWeeks = $week;
if (isset($_GET["numWeeks"])) {
    $numWeeks = $_GET["numWeeks"];
}

$theTeam = "Amish Electricians";
if (isset($_GET["team"])) {
    $theTeam = $_GET["team"];
}

foreach ($tot[$theTeam] as $pos=>$others) {
    $werRes = array("win"=>0, "lose"=>0, "tie"=>0);
    print "<TABLE BORDER=1>";
    print "<TR><TH ALIGN=Center COLSPAN=\"".($numWeeks+1)."\">$pos</TH></TR>";
    print "<TR><TD></TD>";
    for ($week = 1; $week<=$numWeeks; $week++) {
        print "<TD><B>$week</B></TD>";
    }
    foreach($tot as $team=>$remain) {
        print "<TR><TD><B>$team</B></TD>";
        for ($week = 1; $week<=$numWeeks; $week++) {
            $info = $remain[$pos][$week];
//        foreach ($remain[$pos] as $week=>$info) {
            if ($pos=='HC' || $pos=='QB' || $pos=='TE' || $pos=='K' || $pos=='OL') {
                if ($info["count"] < 1) {print "<TD>X</TD>"; continue;}
                if ($team != $theTeam && $tot[$theTeam][$pos][$week]["count"] >= 1) {
                    determineRecord($werRes, $tot[$theTeam][$pos][$week]["pts"], $info["pts"]);
                }
            } else if ($info["count"] < 2) {print "<TD>X</TD>"; continue;}
            else if ($team != $theTeam && $tot[$theTeam][$pos][$week]["count"] >=2) {determineRecord($werRes, $tot[$theTeam][$pos][$week]["pts"], $info["pts"]);}
            print "<TD>".$info["pts"]."</TD>";
        }
        print "</TR>";
    }
    print "<TR><TD COLSPAN=\"".($numWeeks+1)."\" ALIGN=Center>";
    printf ("%d - %d - %d = %5.3f", $werRes["win"], $werRes["lose"], $werRes["tie"], ($werRes["win"]+$werRes["tie"]/2)/($werRes["win"]+$werRes["lose"]+$werRes["tie"]));
    print "</TD></TR>";
    print "</TABLE>";
}

?>
