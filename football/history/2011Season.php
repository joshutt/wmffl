<?
$title = "2011 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2011 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2011Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2011">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2011Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2011Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2011"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs">
<CENTER><H4>League Champions</H4>
    <B>Whiskey Tango</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Crusaders</td><td>64</td><td WIDTH=30%></td><td>Whiskey Tango</td><td>62</td></tr>
<tr><td>Ravaging Camel Clutch</td><td>33</td><td WIDTH=30%></td><td>Crusaders</td><td>54</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Whiskey Tango</td><td>55</td><td WIDTH=30%></td><td>Gallic Warriors</td><td>19</td></tr>
<tr><td>Sacks On The Beach</td><td>15</td><td WIDTH=30%></td><td>Type Os</td><td>0</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">

<?
$thisSeason = 2011;
$thisWeek = 17;
include "common/weekstandings.php";
include "base/footer.html";
?>
