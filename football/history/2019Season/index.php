<?php
$title = "2019 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2019 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="schedule">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections?season=2019">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="/stats/leaders">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="draftresults">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions?year=2019"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs"/>

<CENTER><H4>League Champions</H4>
    <B>Norsemen</B><P>
</CENTER>

<center>
<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Testudos Revenge<td>80</td><td WIDTH=30%></td><td>Norsemen</td><td>114</td></tr>
<tr><td>Richard's Lionhearts</td><td>16</td><td WIDTH=30%></td><td>Testudos Revenge</td><td>23</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Norsemen</td><td>123</td><td WIDTH=30%></td><td>Fighting Squirrels</td><td>34</td></tr>
<tr><td>Crusaders</td><td>7</td><td WIDTH=30%></td><td>British Bulldogs</td><td>8</td></tr>
</TABLE><br/>
</center>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<center>
<?php
$thisSeason = 2019;
$thisWeek = 17;
$clinchedList = array(  'Fighting Squirrels' => 't-',  'Crusaders' => 'y-', 'Norsemen' => 'y-', 'British Bulldogs' => 't-', 'Richard\'s Lionhearts' => 'x-',  'Testudos Revenge' => 'y-');
include "../common/weekstandings.php";
?>
</center>

<?php include "base/footer.html"; ?>
