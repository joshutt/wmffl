<?
require_once "utils/start.php";

$title = "2010 WMFFL Financial Statements";

include "base/menu.php";
?>

<style>
    tr.account1 {background-color: #eeeeee; }
    tr.account2 {background-color: #ffffff; }
    tr.summary {background-color: #cccccc; }
    td.details {font-size: 8pt; text-align: center;}

    .teamList {display: none;}

</style>

<script language="javascript">

    function showDetails(name) {

        var e = document.getElementById(name);
        if (e.style.display == "none") {
            e.style.display = "block";
        } else {
            e.style.display = "none";
        }

    }
    
</script>

<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 1/2/2011</H5>
<HR size = "1">


<TABLE WIDTH=50%>
<TR><TD><B>BALANCES</B></TD></TR>
<TR class="account1">
    <TD>Crusaders</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;91.64</TD>
    <td class="details"><a onclick="showDetails('crusaders');">[Details]</a></td>
</TR>
<TR>
    <TD>Fighting Squirrels</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;21.49</FONT></TD>
    <td class="details"><a onclick="showDetails('squirrels');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Gallic Warriors</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;&nbsp;4.68</font></TD>
    <td class="details"><a onclick="showDetails('gallicwarriors');">[Details]</a></td>
</TR>
<TR>
    <TD>Mansfield Onanists</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;65.64</TD>
    <td class="details"><a onclick="showDetails('onanists');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>MeggaMen</A></TD>
    <TD ALIGN=Right>$&nbsp;200.13</TD>
    <td class="details"><a onclick="showDetails('meggamen');">[Details]</a></td>
</TR>
<TR>
    <TD>Norsemen</A></TD>
    <TD ALIGN=Right>$&nbsp;425.11</TD>
    <td class="details"><a onclick="showDetails('norsemen');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Pretend I'm Not Here</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;10.48</TD>
    <td class="details"><a onclick="showDetails('pretend');">[Details]</a></td>
</TR>
<TR>
    <TD>Ravaging Camel Clutch</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;14.40</font></TD>
    <td class="details"><a onclick="showDetails('rcc');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Sacks on the Beach</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;1.31</TD>
    <td class="details"><a onclick="showDetails('sacks');">[Details]</a></td>
</TR>
<TR>
    <TD>Type Os</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;84.64</TD>
    <td class="details"><a onclick="showDetails('typeos');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Werewolves</A></TD>
    <TD ALIGN=Right>$&nbsp;215.02</TD>
    <td class="details"><a onclick="showDetails('werewolves');">[Details]</a></td>
</TR>
<TR>
    <TD>Whiskey Tango</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;89.20</TD>
    <td class="details"><a onclick="showDetails('whiskeytango');">[Details]</a></td>
</TR>
</TD></TR></TABLE>

<A HREF="money.php">Back to League Finances</A>

<HR size = "1">


<div id="crusaders" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="crusaders"><B>Crusaders</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;5.46</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>2 Days Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>3 Months Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;15.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;90.00</TD></TR>
<TR class="account2"><TD>1 Bad lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>32 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;32.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>10 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;41.60</TD></TR>
<TR class="account1"><TD>1 Tie</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;2.08</TD></TR>
<TR class="account2"><TD>Gold Division Title</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.66</TD></TR>
<TR class="account1"><TD>1st Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;69.84</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$129.00</FONT></B></TD><TD ALIGN=Right><B>$220.64</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="gallicwarriors" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="gallicwarriors"><B>Gallic Warriors</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.64</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>2 Days Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;86.64</TD></TR>
<TR class="account1"><TD>2 Bad lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>14 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;14.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>2 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;8.32</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;99.64</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;94.96</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="squirrels" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="goballs"><B>Fighting Squirrels</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;24.37</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;50.00</TD></TR>
<TR class="account2"><TD>7 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;7.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;29.12</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;82.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;103.49</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="rcc" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="rcc"><B>Ravaging Camel Clutch</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;3.59</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;56.05</TD></TR>
<TR class="account2"><TD>24 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;24.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>6 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;24.96</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;99.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;84.60</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="onanists" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="onanists"><B>Mansfield Onanists</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Paid 2 Year Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;150.00</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>26 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;26.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;16.64</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;101.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;166.64</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="meggamen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="meggamen"><B>MeggaMen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;170.71</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>27 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;27.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>12 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;49.92</TD></TR>
<TR class="account1"><TD>White Division Title</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.66</TD></TR>
<TR class="account2"><TD>1st Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;69.84</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;102.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;302.13</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="norsemen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="norsemen"><B>Norsemen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;170.46</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>57 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;57.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;37.44</TD></TR>
<TR class="account1"><TD>1st Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;69.84</TD></TR>
<TR class="account2"><TD>League Finals</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;104.76</TD></TR>
<TR class="account1"><TD>League Champions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;174.61</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;132.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;557.11</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="pretend" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="pretend"><B>Pretend I'm Not Here</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;12.55</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;62.45</TD></TR>
<TR class="account2"><TD>2 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>3 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;12.48</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;77.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;87.48</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="sacks" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="sacks"><B>Sacks on the Beach</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;76.03</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>33 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;33.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;33.28</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;108.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;109.31</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="typeos" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="typeos"><B>Type Os</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid 2 Year Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;150.00</TD></TR>
<TR class="account2"><TD>1 Bad Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>6 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;16.64</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;82.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;166.64</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="werewolves" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="werewolves"><B>Werewolves</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.45</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;81.45</TD></TR>
<TR class="account2"><TD>17 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;17.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>11 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;45.76</TD></TR>
<TR class="account2"><TD>Burgundy Division Title</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.66</TD></TR>
<TR class="account1"><TD>1st Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;69.84</TD></TR>
<TR class="account2"><TD>League Finals</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;104.76</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;98.45</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;313.47</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="whiskeytango" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="whiskeytango"><B>Whiskey Tango</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;150.00</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>17 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;17.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;29.12</TD></TR>
<TR class="account1"><TD>1 Tie</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;2.08</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;92.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;181.20</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<? include "base/footer.html"; ?>
