<?
require_once "utils/start.php";

if (isset($season)) {
    $lookseason = $season;
} else {
    $lookseason = $currentSeason;
}

//$query = "SELECT t.name, CONCAT(p.firstname, ' ',p.lastname), ";
//$query .= "p.pos, p.team, pro.cost ";
//$query .= "FROM newplayers p, protections pro, teamnames t ";
//$query .= "WHERE p.playerid=pro.playerid ";
//$query .= "AND pro.season=$lookseason and t.teamid=pro.teamid and t.season=pro.season ";

$query = "select t.name, CONCAT(p.firstname, ' ', p.lastname),
  p.pos, r.nflteamid, pro.cost
FROM newplayers p
  JOIN nflrosters r on p.playerid=r.playerid and r.dateon <= concat($lookseason, '-08-15') and (r.dateoff is null or r.dateoff >= concat($lookseason, '-08-15'))
JOIN protections pro on p.playerid=pro.playerid
JOIN teamnames t on t.teamid=pro.teamid and t.season=pro.season
  WHERE pro.season=$lookseason ";


if (!isset($order) || $order=='team') {
    $query .= "ORDER BY t.name, p.pos, p.lastname ";
    $teamcheck = true;
} else {
    $query .= "ORDER BY p.pos, p.lastname ";
    $teamcheck = false;
}

$displayArray = array();
$result = mysql_query($query) or die("Error: ".mysql_error());
while (list($team, $name, $pos, $nfl, $cost) = mysql_fetch_row($result)) {
    if ($teamcheck) {
        $labels = array("Name", "Position", "NFL Team", "Cost");
        if ($oldteam != $team) {
            $teamArray = array();
            $displayArray[$team] = array();
            $oldteam = $team;
        }
        array_push($displayArray[$team], array($name, $pos, $nfl, $cost));
    } else {
        $labels = array("Team", "Name", "NFL Team", "Cost");
        if ($oldpos != $pos) {
            $teamArray = array();
            $displayArray[$pos] = array();
            $oldpos = $pos;
        }
        array_push($displayArray[$pos], array($team, $name, $nfl, $cost));
    }
}


$title = "WMFFL Protections";
?>

<?
        include "base/menu.php"; 
?>      

<H1 ALIGN=Center>Protections</H1>
<HR size = "1">

<P ALIGN=Center><A HREF="showprotections.php?order=team">By Team</A>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<A HREF="showprotections.php?order=pos">By Position</A>
</P>

<TABLE WIDTH=100%>
<?
$result = mysql_query($query) or die("Error: ".mysql_error());

foreach ($displayArray as $key=>$teamArray) {
    print "<tr><th colspan=\"4\">$key (".sizeof($teamArray).")</th></tr>";
    print "<tr>";
    foreach ($labels as $label) {
        print "<th align=\"left\">$label</th>";
    }
    print "</tr>";
    foreach ($teamArray as $team) {
        print "<tr>";
        foreach ($team as $item) {
            print "<td>$item</td>";
        }
        print "</tr>";
    }
    print "<tr><td></td></tr>";
}
?>
</TABLE>

<?
        include "base/footer.html";
?>


