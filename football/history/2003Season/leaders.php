<?
require_once "base/conn.php";

$sql = "SELECT  t.name, p.position, sum(ps.active) as 'totpts' ";
$sql .= "FROM playerscores ps, players p, roster r, team t, weekmap w ";
$sql .= "WHERE ps.playerid=p.playerid and r.playerid=p.playerid ";
$sql .= "and r.teamid=t.teamid and r.dateon <= w.activationdue ";
$sql .= "and (r.dateoff is null or r.dateoff > w.activationdue) ";
$sql .= "and w.season=2003 and ps.season=w.season and ps.week=w.week ";
$sql .= "and ps.week<=14 ";
$sql .= "and ps.active is not null ";
$sql .= "GROUP BY t.name, p.position ";
$sql .= "ORDER BY p.position, 'totpts' DESC ";

$dateQuery = "SELECT max(week) FROM playerscores where season=2003";

$results = mysql_query($sql);
$dateRes = mysql_query($dateQuery);

list($week) = mysql_fetch_row($dateRes);
?>

<HTML>
<HEAD>
<TITLE>League Leaders</TITLE>
</HEAD>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>League Leaders</H1>
<!-- <H5 ALIGN=Center><I>Through Week <?print $week;?></I></H5> -->
<H5 ALIGN=Center><I>Season Final</I></H5>
<HR>

<P>Below is a list of how many points each team has scored at each position
during the course of the season.</P>

<TABLE WIDTH=100% ALIGN=Center>
<TR>
<?
$off = array();
$def = array();
$count = 0;
while ($rank = mysql_fetch_array($results)) {
	if ($count % 3 == 0) {
		print "</TR><TR><TD>&nbsp;</TD></TR><TR>";
	}
	$count++;
	print "<TD><TABLE>";
	print "<TR><TH COLSPAN=2 ALIGN=Center>".$rank["position"]."</TH></TR>";
	print "<TR><TD>".$rank["name"]."</TD><TD>".$rank["totpts"]."</TD></TR>";
    if ($rank["position"] == "DB" || $rank["position"] == "LB" || $rank["position"] == "DL") {
        $def[$rank["name"]] += $rank["totpts"];
    } else {
        $off[$rank["name"]] += $rank["totpts"];
    }
	for ($i=1; $i<10; $i++) {
		$rank = mysql_fetch_array($results);
		print "<TR><TD>".$rank["name"]."</TD><TD>".$rank["totpts"]."</TD></TR>";
        if ($rank["position"] == "DB" || $rank["position"] == "LB" || $rank["position"] == "DL") {
            $def[$rank["name"]] += $rank["totpts"];
        } else {
            $off[$rank["name"]] += $rank["totpts"];
        }
	}
	print "</TABLE></TD>";
}

arsort($off);
arsort($def);

print "<TD><TABLE>";
print "<TR><TH COLSPAN=2 ALIGN=Center>Offense</TH></TR>";
foreach ($off as $team=>$score) {
    print "<TR><TD>$team</TD><TD>$score</TD></TR>";
    $totpts[$team] = $score + $def[$team];
}
print "</TABLE></TD>";
print "<TD><TABLE>";
print "<TR><TH COLSPAN=2 ALIGN=Center>Defense</TH></TR>";
foreach ($def as $team=>$score) {
    print "<TR><TD>$team</TD><TD>$score</TD></TR>";
}
print "</TABLE></TD>";
print "</TR><TR><TD>&nbsp;</TD></TR><TR>";
print "<TD></TD>";
arsort($totpts);
print "<TD><TABLE>";
print "<TR><TH COLSPAN=2 ALIGN=Center>Total Points</TH></TR>";
foreach ($totpts as $team=>$score) {
    print "<TR><TD>$team</TD><TD>$score</TD></TR>";
}
print "</TABLE></TD>";
?>
</TR>
</TABLE>

<?
include "base/footer.html";
?>
