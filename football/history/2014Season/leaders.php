<?
require_once "utils/start.php";

$thisSeason=2014;

$sql = "select t.name, ra.pos, sum(ps.active) as 'totpts'
from playerscores ps
JOIN newplayers p ON ps.playerid=p.playerid
JOIN revisedactivations ra on ra.playerid=ps.playerid and ps.season=ra.season and ps.week=ra.week
JOIN teamnames t ON ra.teamid=t.teamid and ps.season=t.season
WHERE ps.season=$thisSeason
and ps.week<=14
and ps.active is not NULL
GROUP BY t.name, ra.pos
ORDER BY ra.pos, `totpts` DESC";

$dateQuery = "SELECT max(week) FROM playerscores where season=$thisSeason and week<=14";

$results = mysql_query($sql) or die("$sql<br/>".mysql_error());
$dateRes = mysql_query($dateQuery);

list($week) = mysql_fetch_row($dateRes);
$numCol = 3;

$title = "League Leaders";
?>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center><?= $thisSeason ?> League Leaders</H1>
<HR>

<TABLE WIDTH=100% ALIGN=Center> 
<TR>
<?
$off = array();
$def = array();
$count = 0;
while ($rank = mysql_fetch_array($results)) {
	if ($count % $numCol == 0) {
		print "</TR><TR><TD>&nbsp;</TD></TR><TR>";
	}
	$count++;
	print "<TD valign=\"top\"><TABLE>";
	print "<TR><TH COLSPAN=3 ALIGN=Center>".$rank["pos"]."</TH></TR>";
	print "<TR><TD>".$rank["name"]."</TD><td width=\"10\"></td><TD>".$rank["totpts"]."</TD></TR>";
    if ($rank["pos"] == "DB" || $rank["pos"] == "LB" || $rank["pos"] == "DL") {
        $def[$rank["name"]] += $rank["totpts"];
    } else {
        $off[$rank["name"]] += $rank["totpts"];
    }
	for ($i=1; $i<12; $i++) {
		$rank = mysql_fetch_array($results);
		print "<TR><TD>".$rank["name"]."</TD><td width=\"10\"></td><TD>".$rank["totpts"]."</TD></TR>";
        if ($rank["pos"] == "DB" || $rank["pos"] == "LB" || $rank["pos"] == "DL") {
            $def[$rank["name"]] += $rank["totpts"];
        } else {
            $off[$rank["name"]] += $rank["totpts"];
        }
	}
	print "</TABLE></TD>";
}

arsort($off);
arsort($def);

#print "<tr><td>&nbsp;</td></tr>";
#print "<tr><TD><TABLE>";
print "<td><table>";
print "<TR><TH COLSPAN=3 ALIGN=Center>Offense</TH></TR>";
$totpts = array();
foreach ($off as $team=>$score) {
    print "<TR><TD>$team</TD><td width=\"10\"></td><TD>$score</TD></TR>";
    $totpts[$team] = $score + $def[$team];
}
print "</TABLE></TD>";
print "<TD><TABLE>";
print "<TR><TH COLSPAN=3 ALIGN=Center>Defense</TH></TR>";
foreach ($def as $team=>$score) {
    print "<TR><TD>$team</TD><td width=\"10\"></td><TD>$score</TD></TR>";
}
print "</TABLE></TD>";
print "</TR><TR><TD>&nbsp;</TD></TR><TR>";
print "<TD></TD>";
arsort($totpts);
print "<TD><TABLE>";
print "<TR><TH COLSPAN=3 ALIGN=Center>Total Points</TH></TR>";
foreach ($totpts as $team=>$score) {
    print "<TR><TD>$team</TD><td width=\"10\"></td><TD>$score</TD></TR>";
}
print "</TABLE></TD>";
?>
</TR>
</TABLE>

<?
include "base/footer.html";
?>
