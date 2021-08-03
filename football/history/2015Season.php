<?
$title = "2015 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2015 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2015Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2015">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2015Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2015Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2015"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs"/>
<CENTER><H4>League Champions</H4>
    <B>MeggaMen</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>MeggaMen</td><td>28</td><td WIDTH=30%></td><td>MeggaMen</td><td>64</td></tr>
<tr><td>Sean Taylor's Ashes</td><td>20</td><td WIDTH=30%></td><td>Sacks on the Beach</td><td>14</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Sacks on the Beach</td><td>48</td><td WIDTH=30%></td><td>Woodland Rangers</td><td>19</td></tr>
<tr><td>Crusaders</td><td>40</td><td WIDTH=30%></td><td>Whiskey Tango</td><td>0</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<?
$thisSeason = 2015;
$thisWeek = 17;
$clinchedList = array( 'Sean Taylor\'s Ashes' => 'y-', 'Crusaders' => 'y-', 'MeggaMen' => 'y-', 'Sacks on the Beach' => 'x-', "Whiskey Tango" => 'z-', 'Woodland Rangers' => 'z-');
include "common/weekstandings.php";
include "base/footer.php";
?>
