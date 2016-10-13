<?
require_once "utils/start.php";
if ($currentWeek < 1) {
    $thisSeason = $currentSeason - 1;
} else {
    $thisSeason = $currentSeason;
}

if (isset($_REQUEST["season"])) {
    $thisSeason=$_REQUEST["season"];
}

$sql = "SELECT  t.name, p.pos, sum(ps.active) as 'totpts' ";
//$sql .= "FROM playerscores ps, players p, roster r, team t, weekmap w ";
$sql .= "FROM playerscores ps, newplayers p, roster r, teamnames t, weekmap w ";
$sql .= "WHERE ps.playerid=p.playerid and r.playerid=p.playerid ";
$sql .= "and r.teamid=t.teamid and r.dateon <= w.activationdue ";
$sql .= "and (r.dateoff is null or r.dateoff > w.activationdue) ";
$sql .= "and w.season=$thisSeason and ps.season=w.season and ps.week=w.week ";
$sql .= "and ps.week<=14 ";
$sql .= "and t.season=$thisSeason ";
$sql .= "and ps.active is not null ";
$sql .= "GROUP BY t.name, p.pos ";
$sql .= "ORDER BY p.pos, `totpts` DESC ";

$dateQuery = "SELECT max(week) FROM playerscores where season=$thisSeason and week<=14";

$results = mysql_query($sql) or die("$sql<br/>".mysql_error());
$dateRes = mysql_query($dateQuery);

list($week) = mysql_fetch_row($dateRes);
$numCol = 3;

$title = "League Leaders";
?>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>League Leaders</H1>
<H5 ALIGN=Center><I>Through Week <?print $week;?></I></H5>
<!-- <H5 ALIGN=Center><I>Season Final</I></H5> -->
<HR>

<? include "base/statbar.html"; ?>

<P>Below is a list of how many points each team has scored at each position
during the course of the season.</P>

<TABLE WIDTH=100% ALIGN=Center> 
<TR>
<?
$off = array();
$def = array();
$count = 0;
$pos = "";
while ($rank = mysql_fetch_array($results)) {
    if ($rank["pos"] != $pos) {
        $pos = $rank["pos"];
        
        if ($count > 0) {
            print "</TABLE></TD>";
        }
        
        if ($count % $numCol == 0) {
            print "</TR><TR><TD>&nbsp;</TD></TR><TR>";
        }
        $count++;
		
        print "<TD valign=\"top\"><TABLE>";
	print "<TR><TH COLSPAN=3 ALIGN=Center>".$rank["pos"]."</TH></TR>";

    }
    
    print "<TR><TD>".$rank["name"]."</TD><td width=\"10\"></td><TD>".$rank["totpts"]."</TD></TR>";
    if ($rank["pos"] == "DB" || $rank["pos"] == "LB" || $rank["pos"] == "DL") {
        $def[$rank["name"]] += $rank["totpts"];
    } else {
        $off[$rank["name"]] += $rank["totpts"];
    }
 
}
print "</table></td>";

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