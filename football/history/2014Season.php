<?php
$title = "2014 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2014 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="2014Season/schedule.php">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections.php?season=2014">
<IMG SRC="/images/football.jpg" BORDER=0>Protections</A>
</td>

<td>
<A HREF="2014Season/leaders.php">
<IMG SRC="/images/football.jpg" BORDER=0>League Leaders</A>
</td>

<td ALIGN=Left><A HREF="2014Season/draftresults.php">
<IMG SRC="/images/football.jpg" BORDER=0>Draft Results</A></td>
</tr>

<tr><td>&nbsp;<br/></td></tr>
<tr>
<td align="left"><A HREF="#playoffs"><IMG SRC="/images/football.jpg" BORDER=0>Playoffs</A>
</td>

<td>
<A HREF="/transactions/transactions.php?year=2014"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Final Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs"/>
<CENTER><H4>League Champions</H4>
    <B>Joe Gibbs Good Head</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Sacks On The Beach</td><td>39</td><td WIDTH=30%></td><td>Joe Gibbs Good Head</td><td>36</td></tr>
<tr><td>Gallic Warriors</td><td>7</td><td WIDTH=30%></td><td>Sacks on the Beach</td><td>12</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Joe Gibbs Good Head</td><td>46</td><td WIDTH=30%></td><td>Woodland Rangers</td><td>46</td></tr>
<tr><td>Pretend I'm Not Here</td><td>13</td><td WIDTH=30%></td><td>Norsemen</td><td>0</td></tr>
</TABLE><br/>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<?
$thisSeason = 2014;
$thisWeek = 17;
$clinchedList = array( 'Joe Gibbs Good Head' => 'y-', 'Norsemen' => 'z-', 'Gallic Warriors' => 'y-', 'Sacks on the Beach' => 'y-', "Pretend I'm Not Here" => 'x-', 'Woodland Rangers' => 'z-');
include "common/weekstandings.php";
include "base/footer.html";
