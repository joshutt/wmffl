<?php
require_once "utils/connect.php";
$query = "SELECT p.firstname, p.lastname, pc.years, MAX(pos.cost)-MIN(pos.cost) as 'Extra', t.name
FROM newplayers p
 JOIN protectioncost pc on p.playerid=pc.playerid
 JOIN positioncost pos ON p.pos=pos.position and pos.years<=pc.years
LEFT JOIN roster r ON r.playerid=p.playerid AND r.dateon <= '2003-08-25' and (r.dateoff is null or r.DateOff > '2003-09-01')
LEFT JOIN teamnames t on r.teamid=t.teamid and t.season=pc.season
WHERE pc.season=2003
GROUP BY p.playerid, pos.position
ORDER BY t.name, Extra desc, pc.years desc";

$page = array();
$result = mysqli_query($conn, $query);
while ($aLine = mysqli_fetch_array($result)) {
    if (!array_key_exists($aLine['name'], $page)) {
        $page[$aLine['name']] = "";
    }
	$page[$aLine['name']] .= "<TR><TD>".$aLine['firstname']." ".$aLine['lastname'];
	$page[$aLine['name']] .= "</TD><TD ALIGN=Center>".$aLine['years']."</TD>";
	$page[$aLine['name']] .= "<TD ALIGN=Center>+".$aLine['Extra']."</TD></TR>";
}
$title = "2003 WMFFL Protection Costs";
include "base/menu.php";
?>

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
</TABLE>

<P>The adjustment factor for years protected are:
<TABLE BORDER=1>
<TR><TH COLSPAN=9>Years Protected</TH></TR>
<TR><TD></TD><TD>1</TD><TD>2</TD><TD>3</TD><TD>4</TD><TD>5</TD><TD>6</TD><TD>7</TD><TD>8+</TD></TR>
<TR><TD>QB, RB, WR & TE</TD><TD>+1</TD><TD>+2</TD><TD>+3</TD><TD>+4</TD><TD>+4</TD><TD>+5</TD><TD>+5</TD><TD>+6</TD></TR>
<TR><TD>K, OL, DL, LB & DB</TD><TD>+1</TD><TD>+1</TD><TD>+2</TD><TD>+2</TD><TD>+3</TD><TD>+3</TD><TD>+3</TD><TD>+4</TD></TR>
</TABLE>

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
    <?= $page['Crusaders']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Freezer Burn</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Freezer Burn']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Green Wave</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Green Wave']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Illuminati</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Illuminati']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>MeggaMen</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['MeggaMen']; ?>
</TABLE>
</TD><TD WIDTH=*></TD><TD WIDTH=45% VALIGN=Top>

<TABLE ALIGN=Right VALIGN=Top>
<TR><TH COLSPAN=2>Norsemen</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Norsemen']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Rednecks</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Rednecks']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>War Eagles</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['War Eagles']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Werewolves</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Werewolves']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Whiskey Tango</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['Whiskey Tango']; ?>
<TR><TD>&nbsp;</TD></TR>

<TR><TH COLSPAN=2>Not on a Team</TH></TR>
<TR><TH>Player Name</TH><TH>Years Protected</TH><TH>Extra Cost</TH></TR>
    <?= $page['']; ?>

</TABLE>
</TD></TR></TABLE>
<?php include "base/footer.html"; ?>
