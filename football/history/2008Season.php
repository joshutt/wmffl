<?php
$title = "2008 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2008 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2008Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2008">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2008Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2008Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2008"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs">
<CENTER><H4>League Champions</H4>
    <B>Sacks on the Beach</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Sacks on the Beach</td><td>25</td><td WIDTH=30%></td><td>Sacks on the Beach</td><td>73</td></tr>
<tr><td>Lindbergh Baby Casserole</td><td>0</td><td WIDTH=30%></td><td>Crusaders</td><td>45</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toliet Bowl</B></td></tr>
<tr><td>Crusaders</td><td>51</td><td WIDTH=30%></td><td>Fighting Squirrels</td><td>30</td></tr>
<tr><td>Norsemen</td><td>39</td><td WIDTH=30%></td><td>Pretend I'm Not Here</td><td>0</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">

<?php
$thisSeason = 2008;
$thisWeek = 17;
$clinchedList = array('Lindbergh Baby Casserole' => 'y-', 'Sacks on the Beach' => 'x-', 'Crusaders' => 'y-',
    'Norsemen' => 'x-', 'Fighting Squirrels' => 'z-', "Pretend I'm Not Here" => 'z-');
include "common/weekstandings.php";
include "base/footer.php";
