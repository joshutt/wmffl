<?php
require_once "utils/start.php";
$title = "Compare Rosters";

include "base/menu.php";

	$aquery = "SELECT name, teamid FROM team WHERE active=1 ORDER BY name";
	$results = mysql_query($aquery, $conn);
	$outputString = "";
	while ($row = mysql_fetch_array($results)) {
		$outputString .= "<OPTION VALUE=\"".$row["teamid"]."\">";
		$outputString .= $row["name"]."</OPTION>";
	}
?>


<H1 ALIGN=Center>Compare Rosters</H1>
<HR size = "1">

<CEnter>
<FORM ACTION="compareteams.php" METHOD="POST">
<SELECT NAME="teamone">
    <?= $outputString; ?>
</SELECT>
<SELECT NAME="teamtwo">
    <?= $outputString; ?>
</SELECT>
<INPUT TYPE="Submit" VALUE="Compare">
</FORM>

<?
if (array_key_exists('teamone', $_REQUEST) && array_key_exists('teamtwo', $_REQUEST)) {
    $teamone = $_REQUEST['teamone'];
    $teamtwo = $_REQUEST['teamtwo'];
	$thequery = "select concat(p.firstname, ' ', p.lastname) as 'name', p.pos, p.team, ";
	$thequery .= "t.name as 'teamname'";
	$thequery .= "from newplayers p, roster r, team t ";
	$thequery .= "where p.playerid=r.playerid and r.teamid=t.teamid and r.dateoff is null ";
	$thequery .= "and t.teamid in ($teamone, $teamtwo) ";
	$thequery .= "order by t.name, p.pos, p.lastname";
	$result = mysql_query($thequery, $conn) or die("Dead: ".mysql_error());

	$teamname = "";
//	print "<CENTER>";
	print "<TABLE ALIGN=Center BORDER=0>";
	print "<TR><TD VALIGN=Top>";
	while ($row = mysql_fetch_array($result)) {
		if ($row["teamname"] != $teamname) {
			if ($teamname != "") {
				print "</TABLE>";
				print "</TD><TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
				print "<TD VALIGN=Top>";
			}
			print "<TABLE>";
			print "<TR><TH COLSPAN=3>".$row["teamname"]."</TH></TR>";
			$teamname = $row["teamname"];
		}
		print "<TR><TD>".$row['name']."</TD><TD>".$row['pos']."</TD><TD>".$row['team']."</TD></TR>";
	}
	print "</TABLE></TD></TR></TABLE>";
}
?>
</CENTER>
<? include "base/footer.html"; ?>
