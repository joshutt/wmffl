<?
$title = "2012 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2012 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2012Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2012">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2012Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2012Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2012"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
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
<tr><td>Sacks On The Beach</td><td>65</td><td WIDTH=30%></td><td>Whiskey Tango</td><td>31</td></tr>
<tr><td>Werewolves</td><td>31</td><td WIDTH=30%></td><td>Sacks on the Beach</td><td>7</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Whiskey Tango</td><td>24</td><td WIDTH=30%></td><td>Gallic Warriors</td><td>54</td></tr>
<tr><td>Mansfield Onanists</td><td>7</td><td WIDTH=30%></td><td>MeggaMen</td><td>18</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">

<?
$thisSeason = 2012;
$thisWeek = 17;
include "common/weekstandings.php";
include "base/footer.html";
?>
