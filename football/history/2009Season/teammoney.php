<?
require_once "utils/start.php";

$title = "2009 WMFFL Financial Statements";

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
<H5 ALIGN=Center>Last Updated 1/20/2010</H5>
<HR size = "1">


<TABLE WIDTH=50%>
<TR><TD><B>BALANCES</B></TD></TR>
<TR class="account1">
    <TD>Crusaders</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;5.46</font></TD>
    <td class="details"><a onclick="showDetails('crusaders');">[Details]</a></td>
</TR>
<TR>
    <TD>Fighting Squirrels</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;24.37</FONT></TD>
    <td class="details"><a onclick="showDetails('squirrels');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Gallic Warriors</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;&nbsp;2.64</font></TD>
    <td class="details"><a onclick="showDetails('gallicwarriors');">[Details]</a></td>
</TR>
<TR>
    <TD>Lindbergh Baby Casserole</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;3.59</TD>
    <td class="details"><a onclick="showDetails('lindbergh');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>MeggaMen</A></TD>
    <TD ALIGN=Right>$&nbsp;170.71</TD>
    <td class="details"><a onclick="showDetails('meggamen');">[Details]</a></td>
</TR>
<TR>
    <TD>Norsemen</A></TD>
    <TD ALIGN=Right>$&nbsp;170.46</TD>
    <td class="details"><a onclick="showDetails('norsemen');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Pretend I'm Not Here</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;12.55</TD>
    <td class="details"><a onclick="showDetails('pretend');">[Details]</a></td>
</TR>
<TR>
    <TD>Sacks on the Beach</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;76.03</TD>
    <td class="details"><a onclick="showDetails('sacks');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Werewolves</A></TD>
    <TD ALIGN=Right><font color="red">- $&nbsp;&nbsp;&nbsp;6.45</font></TD>
    <td class="details"><a onclick="showDetails('werewolves');">[Details]</a></td>
</TR>
<TR>
    <TD>Whiskey Tango</A></TD>
    <TD ALIGN=Right>$&nbsp;150.00</TD>
    <td class="details"><a onclick="showDetails('whiskeytango');">[Details]</a></td>
</TR>
</TD></TR></TABLE>

<A HREF="money.php">Back to League Finances</A>

<HR size = "1">


<div id="crusaders" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="crusaders"><B>Crusaders</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;7.75</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>1 Day Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;68.25</TD></TR>
<TR class="account1"><TD>2 Bad lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>16 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;16.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>6 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;23.46</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;94.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;99.46</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="gallicwarriors" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="gallicwarriors"><B>Gallic Warriors</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;8.64</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>1 Day Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>1 Month Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;5.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;67.36</TD></TR>
<TR class="account2"><TD>25 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;25.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;27.37</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;106.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;103.37</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="squirrels" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="goballs"><B>Fighting Squirrels</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;22.73</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;52.27</TD></TR>
<TR class="account2"><TD>1 Bad Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>2 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;2.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;27.37</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;78.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;102.37</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="lindbergh" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="lindbergh"><B>Lindbergh Baby Casserole</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;80.95</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>4 Bad Lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;4.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>14 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;14.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;15.64</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;93.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;96.59</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="meggamen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="meggamen"><B>MeggaMen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;5.97</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;80.97</TD></TR>
<TR class="account2"><TD>15 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;15.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.19</TD></TR>
<TR class="account2"><TD>Division Title</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;13.68</TD></TR>
<TR class="account1"><TD>First Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;54.73</TD></TR>
<TR class="account2"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;82.10</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;95.97</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;266.67</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="norsemen" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="norsemen"><B>Norsemen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;9.08</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>1 Day Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>4 Months Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;20.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>47 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;47.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.19</TD></TR>
<TR class="account1"><TD>Division Title</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;13.68</TD></TR>
<TR class="account2"><TD>First Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;54.73</TD></TR>
<TR class="account1"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;82.10</TD></TR>
<TR class="account2"><TD>League Championship</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;136.84</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;152.08</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;322.54</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="pretend" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="pretend"><B>Pretend I'm Not Here</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;21.15</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>1 Day Late</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;54.85</TD></TR>
<TR class="account1"><TD>7 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;7.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;19.55</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;83.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;95.55</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="sacks" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="sacks"><B>Sacks on the Beach</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;330.11</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>1 Bad Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>23 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;23.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.19</TD></TR>
<TR class="account2"><TD>First Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;54.73</TD></TR>
<TR class="account1"><TD>Cash Payment</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;245.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;344.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;420.03</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="werewolves" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="werewolves"><B>Werewolves</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;64.38</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;10.62</TD></TR>
<TR class="account2"><TD>26 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;26.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;19.55</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;101.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;94.55</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="whiskeytango" class="teamList">
<TABLE WIDTH=100%>
<tr><td width="50%"></td><td width="25%"></td><td width="25%"></td></tr>
<TR><TD><A NAME="whiskeytango"><B>Whiskey Tango</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;243.80</TD></TR>
<TR class="account2"><TD>Trophy Shipping</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;61.35</TD></TR>
<TR class="account1"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>31 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;31.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;35.19</TD></TR>
<TR class="account2"><TD>First Round Playoffs</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;54.73</TD></TR>
<TR class="account1"><TD>Cash Payment</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;139.07</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;245.07</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;395.07</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<? include "base/footer.html"; ?>
