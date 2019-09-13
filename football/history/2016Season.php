<?
$title = "2016 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2016 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2016Season/schedule">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2016">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2016Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2016Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2016"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs"/>
<CENTER><H4>League Champions</H4>
    <B>Woodland Rangers</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>MeggaMen</td><td>46</td><td WIDTH=30%></td><td>Woodland Rangers</td><td>63</td></tr>
<tr><td>Gallic Warriors</td><td>11</td><td WIDTH=30%></td><td>MeggaMen</td><td>27</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Woodland Rangers</td><td>68</td><td WIDTH=30%></td><td>Pretend I'm Not Here</td><td>55</td></tr>
<tr><td>Amish Electricians</td><td>66</td><td WIDTH=30%></td><td>Sean Taylor's Ashes</td><td>0</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<?
$thisSeason = 2016;
$thisWeek = 17;
$clinchedList = array( 'Sean Taylor\'s Ashes' => 'z-', 'Gallic Warriors' => 'y-', 'MeggaMen' => 'y-', 'Amish Electricians' => 'y-', "Pretend I'm Not Here" => 'z-', 'Woodland Rangers' => '-');
include "common/weekstandings.php";
include "base/footer.html";
?>
