<?
require_once "utils/start.php";

$title = "2011 WMFFL Financial Statements";

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
<H5 ALIGN=Center>Last Updated 6/18/2012</H5>
<HR size = "1">


<TABLE WIDTH=50%>
<TR><TD><B>BALANCES</B></TD></TR>
<TR class="account1">
    <TD>Crusaders</A></TD>
    <TD ALIGN=Right>$&nbsp;202.58</TD>
    <td class="details"><a onclick="showDetails('crusaders');">[Details]</a></td>
</TR>
<TR>
    <TD>Fighting Squirrels</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;&nbsp;2.81</FONT></TD>
    <td class="details"><a onclick="showDetails('squirrels');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Gallic Warriors</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;&nbsp;3.20</font></TD>
    <td class="details"><a onclick="showDetails('gallicwarriors');">[Details]</a></td>
</TR>
<TR>
    <TD>Mansfield Onanists</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;7.74</TD>
    <td class="details"><a onclick="showDetails('onanists');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>MeggaMen</A></TD>
    <TD ALIGN=Right>$&nbsp;128.33</TD>
    <td class="details"><a onclick="showDetails('meggamen');">[Details]</a></td>
</TR>
<TR>
    <TD>Norsemen</A></TD>
    <TD ALIGN=Right>$&nbsp;339.31</TD>
    <td class="details"><a onclick="showDetails('norsemen');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Pretend I'm Not Here</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;13.50</TD>
    <td class="details"><a onclick="showDetails('pretend');">[Details]</a></td>
</TR>
<TR>
    <TD>Ravaging Camel Clutch</A></TD>
    <TD ALIGN=Right>$&nbsp;102.28</TD>
    <td class="details"><a onclick="showDetails('rcc');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Sacks on the Beach</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;90.90</TD>
    <td class="details"><a onclick="showDetails('sacks');">[Details]</a></td>
</TR>
<TR>
    <TD>Type Os</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;9.54</TD>
    <td class="details"><a onclick="showDetails('typeos');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Werewolves</A></TD>
    <TD ALIGN=Right>$&nbsp;153.23</TD>
    <td class="details"><a onclick="showDetails('werewolves');">[Details]</a></td>
</TR>
<TR>
    <TD>Whiskey Tango</A></TD>
    <TD ALIGN=Right>$&nbsp;361.34</TD>
    <td class="details"><a onclick="showDetails('whiskeytango');">[Details]</a></td>
</TR>
</TD></TR></TABLE>

<A HREF="money.php">Back to League Finances</A>

<HR size = "1">


<div id="crusaders" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="crusaders"><B>Crusaders</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;91.65</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>32 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;32.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>11 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;42.90</TD></TR>
<TR class="account1"><TD>Gold Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;10.96</TD></TR>
<TR class="account2"><TD>Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;65.63</TD></TR>
<TR class="account1"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;98.44</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$107.00</FONT></B></TD><TD ALIGN=Right><B>$309.58</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="gallicwarriors" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="gallicwarriors"><B>Gallic Warriors</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;4.68</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;79.68</TD></TR>
<TR class="account2"><TD>11 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;11.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>2 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;7.80</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;90.68</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;87.48</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="squirrels" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="goballs"><B>Fighting Squirrels</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;21.49</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;51.00</TD></TR>
<TR class="account2"><TD>12 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;12.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>3 Win</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.70</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;87.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;84.19</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="rcc" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="rcc"><B>Ravaging Camel Clutch</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;14.40</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;92.00</TD></TR>
<TR class="account2"><TD>12 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;12.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.10</TD></TR>
<TR class="account2"><TD>Burgundy Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;10.96</TD></TR>
<TR class="account1"><TD>Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;65.63</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;101.40</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;203.69</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="onanists" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="onanists"><B>Mansfield Onanists</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;65.64</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>17 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;17.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>1 Illegal Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.10</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;93.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;100.74</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="meggamen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="meggamen"><B>MeggaMen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;200.13</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>28 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;28.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.20</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;103.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;231.33</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="norsemen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="norsemen"><B>Norsemen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;425.11</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>41 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;41.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>1 Illegal Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.20</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;117.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;456.31</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="pretend" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="pretend"><B>Pretend I'm Not Here</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;10.48</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;64.52</TD></TR>
<TR class="account2"><TD>6 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;19.50</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;81.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;94.50</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="sacks" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="sacks"><B>Sacks on the Beach</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;1.31</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;75.00</TD></TR>
<TR class="account2"><TD>26 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;26.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>10 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;39.00</TD></TR>
<TR class="account2"><TD>White Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;10.96</TD></TR>
<TR class="account1"><TD>Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;65.63</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;101.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;191.90</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="typeos" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="typeos"><B>Type Os</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;84.64</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>4 Illegal Lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;4.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>1 Win</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;3.90</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;79.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;88.54</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="werewolves" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="werewolves"><B>Werewolves</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;215.03</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>18 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;18.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.20</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;93.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;246.23</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="whiskeytango" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="whiskeytango"><B>Whiskey Tango</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;89.20</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>20 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;20.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>10 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;39.00</TD></TR>
<TR class="account1"><TD>Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;65.63</TD></TR>
<TR class="account2"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;98.44</TD></TR>
<TR class="account1"><TD>League Champions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;164.07</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;95.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;456.34</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<? include "base/footer.html"; ?>
