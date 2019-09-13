<?
$title = "2013 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2013 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2013Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2013">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2013Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2013Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2013"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs">
<CENTER><H4>League Champions</H4>
    <B>MeggaMen</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>MeggaMen</td><td>54</td><td WIDTH=30%></td><td>MeggaMen</td><td>23</td></tr>
<tr><td>Crusaders</td><td>41</td><td WIDTH=30%></td><td>Norsemen</td><td>9</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Norsemen</td><td>89</td><td WIDTH=30%></td><td>Werewolves</td><td>51</td></tr>
<tr><td>Gallic Warriors</td><td>27</td><td WIDTH=30%></td><td>Woodland Rangers</td><td>16</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">

<A NAME="standings">
<?
$thisSeason = 2013;
$thisWeek = 17;
$clinchedList = array( 'Woodland Rangers' => 'z-', 'Norsemen' => 'y-', 'Werewolves' => 'z-', 'Crusaders' => 'y-', 'MeggaMen' => 'y-', 'Gallic Warriors' => 'x-');
include "common/weekstandings.php";
include "base/footer.html";
?>
