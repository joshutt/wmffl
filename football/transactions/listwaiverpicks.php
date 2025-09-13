<?php
/**
 * @var $currentWeek int
 * @var $currentSeason int
 * @var $conn mysqli
 */

require_once "utils/start.php";

if (array_key_exists('week', $_REQUEST)) {
    $week = $_REQUEST['week'];
} else {
//    $week = $currentWeek - 1;
    $week = $currentWeek;
}
if ($currentWeek == 0) {
    $week = 0;
}
#$week = 0;

$sql = "select wa.pick, tn.name, p.firstname, p.lastname, p.pos, p.team
from waiveraward wa, teamnames tn, newplayers p 
where wa.season=$currentSeason and wa.week=$week and wa.teamid=tn.teamid and wa.playerid=p.playerid and tn.season=wa.season
order by wa.pick ";
$results = mysqli_query($conn, $sql);


if (mysqli_num_rows($results) == 0) {
    print "No Waiver pickups last week";
} else {
    print "<table width=100%>";
    while ($pickList = mysqli_fetch_row($results)) {
        print "<tr><td>".$pickList[0].".</td><td>".$pickList[1];
        print "</td><td>".$pickList[2]." ".$pickList[3];
        print " (".$pickList[4]."-".$pickList[5].")</td></tr>";
    }
    print "</table>";
}


