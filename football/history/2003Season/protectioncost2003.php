<?
require_once "$DOCUMENT_ROOT/base/conn.php";
$query = "SELECT p.firstname, p.lastname, pc.years, MAX(pos.cost)-MIN(pos.cost) as 'Extra', t.name ";
$query .= "FROM players p, protectioncost pc, positioncost pos ";
$query .= "LEFT JOIN roster r ON r.playerid=p.playerid AND r.dateoff is null ";
$query .= "LEFT JOIN team t on r.teamid=t.teamid ";
$query .= "WHERE p.playerid=pc.playerid and pc.season=2003 ";
$query .= "and p.position=pos.position and pos.years<=pc.years ";
$query .= "GROUP BY p.playerid, pos.position ";
$query .= "ORDER BY t.name, Extra desc, pc.years desc";

$result = mysql_query($query, $conn);	
while ($aLine = mysql_fetch_array($result)) {
	$page[$aLine['name']] .= "<TR><TD>".$aLine['firstname']." ".$aLine['lastname'];
	$page[$aLine['name']] .= "</TD><TD ALIGN=Center>".$aLine['years']."</TD>";
	$page[$aLine['name']] .= "<TD ALIGN=Center>+".$aLine['Extra']."</TD></TR>";
}
?>

<HTML>
<HEAD>
<TITLE>2003 WMFFL Protection Costs</TITLE>
</HEAD>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<H1 Align=Center>Protection Costs</H1>
<HR size = "1">
<P>
Protections will be based on a point system.  Every
team will be allocated 65 points with which to protect players.  Each player
will be worth a number of points based on position and years he has been
protected.  The base position points are as follows:
<TABLE BORDER=1>
<TR><TD>QB</TD><TD>RB</TD><TD>WR</TD><TD>TE</TD><TD>K</TD><TD>OL</TD><TD>DL</TD><TD>LB</TD><TD>DB</TD></TR>
<TR><TD>9</TD><TD>10</TD><TD>8</TD><TD>4</TD><TD>1</TD><TD>1</TD><TD>3</TD><TD>5</TD><TD>4</TD></TR>
</TABLE></P>

<P>The adjustment factor for years protected are:
<TABLE BORDER=1>
<TR><TH COLSPAN=9>Years Protected</TH></TR>
<TR><TD></TD><TD>1</TD><TD>2</TD><TD>3</TD><TD>4</TD><TD>5</TD><TD>6</TD><TD>7</TD><TD>8+</TD></TR>
<TR><TD>QB, RB, WR & TE</TD><TD>+1</TD><TD>+2</TD><TD>+3</TD><TD>+4</TD><TD>+4</TD><TD>+5</TD><TD>+5</TD><TD>+6</TD></TR>
<TR><TD>K, OL, DL, LB & DB</TD><TD>+1</TD><TD>+1</TD><TD>+2</TD><TD>+2</TD><TD>+3</TD><TD>+3</TD><TD>+3</TD><TD>+4</TD></TR>
</TABLE></P>

<P>Adding the postion cost and adjustment factor together will give you
a player's protection cost.  As many players as desired may be protected,
so long as a team does not exceed their point limit.  Points may be traded.
Any points not used on protections will become transaction points.  There
will be no free transaction points this year.</P>

<P>Below is a list of number of years that players have been protected.
If a player does not appear on this list, their adjustment factor will
be 0.  By taking the player's position and the number below you should
be able to find out how much each player will cost to protect.</P>

<TABLE WIDTH=100%>
<TR><TD WIDTH="45%" VALIGN=Top>

<TABLE ALIGN=Left>

<TR><TH COLSPAN=2>Crusaders</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Crusaders']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Freezer Burn</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Freezer Burn']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Green Wave</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Green Wave']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Illuminati</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Illuminati']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>MeggaMen</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['MeggaMen']; ?>
</TABLE>
</TD><TD WIDTH=*></TD><TD WIDTH=45% VALIGN=Top>

<TABLE ALIGN=Right VALIGN=Top>
<TR><TH COLSPAN=2>Norsemen</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Norsemen']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Rednecks</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Rednecks']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>War Eagles</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['War Eagles']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Werewolves</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Werewolves']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Whiskey Tango</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['Whiskey Tango']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Not on a Team</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
<? print $page['']; ?>

</TABLE>
</TD></TR></TABLE>
<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
