<?php
$title = "2018 WMFFL Season";
include "base/menu.php"; 
?>

<H1 ALIGN=CENTER>The 2018 Season</H1>
<HR size = "1">
<br/>
<TABLE ALIGN=CENTER WIDTH=100%>
<tr>

<td ALIGN=Left><A HREF="schedule">
<IMG SRC="/images/football.jpg" BORDER=0>Schedule</A></td>

<td>
<A HREF="/transactions/showprotections?season=2018">
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
<A HREF="/transactions/transactions?year=2018"><IMG SRC="/images/football.jpg" BORDER=0>Transactions</A>
</td>

<td ALIGN=Left><A HREF="#standings"><IMG SRC="/images/football.jpg" BORDER=0>Current Standings</A></td>
</tr>
</TABLE>

<HR size = "1">
<A NAME="playoffs"/>

<CENTER><H4>League Champions</H4>
    <B>Trump Molests Collies</B><P>
</CENTER>

<TABLE>
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Norsemen</td><td>21</td><td WIDTH=30%></td><td>Trump Molests Collies</td><td>56</td></tr>
<tr><td>MeggaMen</td><td>0</td><td WIDTH=30%></td><td>Norsemen</td><td>50</td></tr>
<tr><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Trump Molests Collies</td><td>46</td><td WIDTH=30%></td><td>Amish Electricians</td><td>41</td></tr>
<tr><td>Fightin' Bitin' Beavers</td><td>44</td><td WIDTH=30%></td><td>Fighting Squirrels</td><td>0</td></tr>
</TABLE><br/>

<br/>

<HR size = "1">
    <A NAME="standings"/>

<?
$thisSeason = 2018;
$thisWeek = 17;
$clinchedList = array('Trump Molests Collies' => 'y-', 'Amish Electricians' => 'z-', 'Fighting Squirrels' => 'z-', 'MeggaMen' => 'x-',  'Norsemen' => 'y-', 'Fightin\' Bitin\' Beavers' => 'y-');
include "../common/weekstandings.php";

include "base/footer.php";
?>
