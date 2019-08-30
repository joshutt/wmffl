<?
$title = "2009 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2009 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2009Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2009">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2009Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2009Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2009"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs">
<CENTER><H4>League Champions</H4>
    <B>Norsemen</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Norsemen</td><td>50</td><td WIDTH=30%></td><td>Norsemen</td><td>60 - OT</td></tr>
<tr><td>Sacks on the Beach</td><td>21</td><td WIDTH=30%></td><td>MeggaMen</td><td>44</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toliet Bowl</B></td></tr>
<tr><td>MeggaMen</td><td>22</td><td WIDTH=30%></td><td>Crusaders</td><td>58</td></tr>
<tr><td>Whiskey Tango</td><td>20</td><td WIDTH=30%></td><td>Lindbergh Baby Casserole</td><td>36</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">

<?
$thisSeason = 2009;
$thisWeek = 17;
include "common/weekstandings.php";
include "base/footer.html";
?>
