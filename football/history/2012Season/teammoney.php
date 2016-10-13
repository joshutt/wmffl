<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$title = "2012 WMFFL Financial Statements";

include "$DOCUMENT_ROOT/base/menu.php";
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
<H5 ALIGN=Center>Last Updated 1/26/2013</H5>
<HR size = "1">


<TABLE WIDTH=50%>
<TR><TD><B>BALANCES</B></TD></TR>
<TR class="account1">
    <TD>Crusaders</A></TD>
    <TD ALIGN=Right>$&nbsp;122.42</TD>
    <td class="details"><a onclick="showDetails('crusaders');">[Details]</a></td>
</TR>
<TR>
    <TD>Fighting Squirrels</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;13.92</FONT></TD>
    <td class="details"><a onclick="showDetails('squirrels');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Gallic Warriors</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;10.08</font></TD>
    <td class="details"><a onclick="showDetails('gallicwarriors');">[Details]</a></td>
</TR>
<TR>
    <TD>Mansfield Onanists</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;75.58</font></TD>
    <td class="details"><a onclick="showDetails('onanists');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>MeggaMen</A></TD>
    <TD ALIGN=Right>$&nbsp;42.23</TD>
    <td class="details"><a onclick="showDetails('meggamen');">[Details]</a></td>
</TR>
<TR>
    <TD>Norsemen</A></TD>
    <TD ALIGN=Right>$&nbsp;267.15</TD>
    <td class="details"><a onclick="showDetails('norsemen');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Pretend I'm Not Here</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;9.90</TD>
    <td class="details"><a onclick="showDetails('pretend');">[Details]</a></td>
</TR>
<TR>
    <TD>Ravaging Camel Clutch</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;54.12</TD>
    <td class="details"><a onclick="showDetails('rcc');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Sacks on the Beach</A></TD>
    <TD ALIGN=Right>$&nbsp;206.80</TD>
    <td class="details"><a onclick="showDetails('sacks');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Werewolves</A></TD>
    <TD ALIGN=Right>$&nbsp;176.96</TD>
    <td class="details"><a onclick="showDetails('werewolves');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Whiskey Tango</A></TD>
    <TD ALIGN=Right>$&nbsp;359.78</TD>
    <td class="details"><a onclick="showDetails('whiskeytango');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Woodland Rangers</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;66.14</font></TD>
    <td class="details"><a onclick="showDetails('woodland');">[Details]</a></td>
</TR>
</TD></TR></TABLE>

<A HREF="money.php">Back to League Finances</A>

<HR size = "1">


<div id="crusaders" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="crusaders"><B>Crusaders</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;202.58</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>37 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;37.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.84</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$112.00</FONT></B></TD><TD ALIGN=Right><B>$234.42</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="gallicwarriors" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="gallicwarriors"><B>Gallic Warriors</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;3.20</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Late Fees</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;11.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;78.20</TD></TR>
<TR class="account1"><TD>15 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;15.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;15.92</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;104.20</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;94.12</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="squirrels" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="goballs"><B>Fighting Squirrels</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.81</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;77.81</TD></TR>
<TR class="account2"><TD>2 Bad Lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;15.92</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;79.81</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;93.73</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="rcc" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="rcc"><B>Ravaging Camel Clutch</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;102.28</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>5 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;5.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.84</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;80.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;134.12</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="onanists" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="onanists"><B>Mansfield Onanists</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;7.74</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;67.26</TD></TR>
<TR class="account2"><TD>1 Bad Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>26 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;26.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.82</TD></TR>
<TR class="account1"><TD>First Round of Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;66.76</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;102.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;177.58</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="meggamen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="meggamen"><B>MeggaMen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;128.33</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>31 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;31.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;19.90</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;106.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;148.23</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="norsemen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="norsemen"><B>Norsemen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;339.31</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>29 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;29.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.84</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;104.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;371.15</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="pretend" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="pretend"><B>Pretend I'm Not Here</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;13.50</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;61.50</TD></TR>
<TR class="account2"><TD>1 Bad Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;9.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;19.90</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;85.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;94.90</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="sacks" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="sacks"><B>Sacks on the Beach</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;90.90</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>19 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;19.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;31.84</TD></TR>
<TR class="account1"><TD>Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.15</TD></TR>
<TR class="account2"><TD>First Round of Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;66.76</TD></TR>
<TR class="account1"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;100.14</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;94.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;300.80</B></TD></TR>
<tr><td></td></tr>
</table>
</div>


<div id="werewolves" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="werewolves"><B>Werewolves</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;153.23</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>15 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;15.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.82</TD></TR>
<TR class="account1"><TD>Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.15</TD></TR>
<TR class="account2"><TD>First Round of Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;66.76</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;90.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;266.96</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="whiskeytango" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="whiskeytango"><B>Whiskey Tango</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;361.34</TD></TR>
<TR class="account2"><TD>Partial Payout</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;276.34</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>31 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;31.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.82</TD></TR>
<TR class="account2"><TD>Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;11.15</TD></TR>
<TR class="account1"><TD>First Round of Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;66.76</TD></TR>
<TR class="account2"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;100.14</TD></TR>
<TR class="account1"><TD>League Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;166.91</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;382.34</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;742.12</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="woodland" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="woodland"><B>Woodland Rangers</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Two Year Pre-pay Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;150.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Paid One year Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;75.00</TD></TR>
<TR class="account1"><TD>Late Fees</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>1 Bad Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>12 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;12.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;27.86</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;169.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;102.86</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
