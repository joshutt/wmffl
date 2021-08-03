<?
$title = "2017 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2017 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2017Season/schedule">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections?season=2017">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2017Season/leaders">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2017Season/draftresults">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions?year=2017"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs"/>
<CENTER><H4>League Champions</H4>
    <B>Amish Electricians</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Sacks on the Beach</td><td>43</td><td WIDTH=30%></td><td>Amish Electricians</td><td>24</td></tr>
<tr><td>Crusaders</td><td>39</td><td WIDTH=30%></td><td>Sacks on the Beach</td><td>10</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Amish Electricians</td><td>74</td><td WIDTH=30%></td><td>Tim Always Pulls Out Late</td><td>88</td></tr>
<tr><td>Fightin' Bitin' Beavers</td><td>37</td><td WIDTH=30%></td><td>Sean Taylor's Ashes</td><td>0</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<?
$thisSeason = 2017;
$thisWeek = 17;
$clinchedList = array( 'Sean Taylor\'s Ashes' => 'z-', 'Crusaders' => 'y-', "Fightin' Bitin' Beavers" => 'y-', 'Amish Electricians' => 'y-', "Tim Always Pulls Out Late" => 'z-', 'Sacks on the Beach' => 'x-');
include "common/weekstandings.php";
include "base/footer.php";
?>
