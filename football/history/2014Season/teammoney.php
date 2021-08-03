<?
require_once "utils/start.php";

$title = "2014 WMFFL Financial Statements";

include "base/menu.php";
?>

<style>
    tr.account1 {background-color: #eeeeee; }
    tr.account2 {background-color: #ffffff; }
    tr.summary {background-color: #cccccc; }
    td.details {font-size: 8pt; text-align: center;}
    td {padding: 5px;}

    .teamList {display: none;}
    .debt {color: red;}

    #crusaders {display: none;]

</style>

<script language="javascript">

    function showDetails(name) {

        var e = document.getElementById(name);
        if (e.style.display == "none" || e.style.display == "") {
            e.style.display = "block";
        } else {
            e.style.display = "none";
        }

    }
    
</script>

<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 1/2/2015</H5>
<HR size = "1">


<TABLE>
<TR><TD><B>BALANCES</B></TD></TR>
<TR class="account1">
    <TD>Crusaders</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;75.77</TD>
    <td class="details"><a onclick="showDetails('crusaders');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Fighting Squirrels</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;97.40</TD>
    <td class="details"><a onclick="showDetails('squirrels');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Gallic Warriors</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;94.01</TD>
    <td class="details"><a onclick="showDetails('gallicwarriors');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Joe Gibbs Good Head</A></TD>
    <TD ALIGN=Right>$&nbsp;408.38</TD>
    <td class="details"><a onclick="showDetails('joegibbs');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Mansfield Onanists</A></TD>
    <TD ALIGN=Right><span class="debt">($&nbsp;&nbsp;65.21)</span></TD>
    <td class="details"><a onclick="showDetails('onanists');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>MeggaMen</A></TD>
    <TD ALIGN=Right>$&nbsp;303.50</TD>
    <td class="details"><a onclick="showDetails('meggamen');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Norsemen</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;74.91</TD>
    <td class="details"><a onclick="showDetails('norsemen');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Pretend I'm Not Here</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;51.00</TD>
    <td class="details"><a onclick="showDetails('pretend');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Sacks on the Beach</A></TD>
    <TD ALIGN=Right>$&nbsp;265.51</TD>
    <td class="details"><a onclick="showDetails('sacks');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Sean Taylor's Ashes</A></TD>
    <TD ALIGN=Right>$&nbsp;&nbsp;13.74</TD>
    <td class="details"><a onclick="showDetails('seantaylor');">[Details]</a></td>
</TR>
<TR class="account1">
    <TD>Whiskey Tango</A></TD>
    <TD ALIGN=Right>$&nbsp;216.62</TD>
    <td class="details"><a onclick="showDetails('whiskeytango');">[Details]</a></td>
</TR>
<TR class="account2">
    <TD>Woodland Rangers</A></TD>
    <TD ALIGN=Right><span class="debt">($&nbsp;&nbsp;55.31)</span></TD>
    <td class="details"><a onclick="showDetails('woodland');">[Details]</a></td>
</TR>
</TD></TR></TABLE>

<A HREF="money.php">Back to League Finances</A>

<HR size = "1">


<div id="crusaders" class="teamList">
<TABLE>
<TR><TD><A NAME="crusaders"><B>Crusaders</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;138.37</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>19 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;19.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>3 Illegal Lineups</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;3.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;34.40</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;97.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;172.77</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="squirrels" class="teamList">
<TABLE>
<TR><TD><A NAME="squirrels"><B>Fighting Squirrels</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Paid 2 Year Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;150.00</TD></TR>
<TR class="account1"><TD>12 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;12.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;34.40</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;87.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;184.40</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="gallicwarriors" class="teamList">
<TABLE>
<TR><TD><A NAME="gallicwarriors"><B>Gallic Warriors</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;81.21</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>31 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;31.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;34.40</TD></TR>
<TR class="account1"><TD>Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;12.08</TD></TR>
<TR class="account2"><TD>Playoff Team</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;72.32</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;106.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;200.01</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="seantaylor" class="teamList">
<TABLE>
<TR><TD><A NAME="seantaylor"><B>Sean Taylor's Ashes</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;24.24</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Paid Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;52.00</TD></TR>
<TR class="account2"><TD>9 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;9.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;21.50</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;84.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;97.74</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="onanists" class="teamList">
<TABLE>
<TR><TD><A NAME="onanists"><B>Mansfield Onanists</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;6.54</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>Late Fees</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;11.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>18 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;18.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;30.10</TD></TR>
<TR class="account1"><TD>1 Tie</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;2.15</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;104.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;38.79</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="meggamen" class="teamList">
<TABLE>
<TR><TD><A NAME="meggamen"><B>MeggaMen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;391.40</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>43 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;43.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>7 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;30.10</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;118.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;421.50</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="norsemen" class="teamList">
<TABLE>
<TR><TD><A NAME="norsemen"><B>Norsemen</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;174.71</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>42 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;42.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;17.20</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;117.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;191.91</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="pretend" class="teamList">
<TABLE>
<TR><TD><A NAME="pretend"><B>Pretend I'm Not Here</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;26.28</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Late Fees</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;6.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>1 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;1.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;34.40</TD></TR>
<TR class="account2"><TD>Playoff Team</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;72.32</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;82.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;133.00</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="sacks" class="teamList">
<TABLE>
<TR><TD><A NAME="sacks"><B>Sacks on the Beach</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;137.08</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>26 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;26.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>8 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;34.40</TD></TR>
<TR class="account1"><TD>1 Tie</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;2.15</TD></TR>
<TR class="account2"><TD>Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;12.08</TD></TR>
<TR class="account1"><TD>Playoff Team</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;72.32</TD></TR>
<TR class="account2"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;108.48</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;101.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;366.51</B></TD></TR>
<tr><td></td></tr>
</table>
</div>


<div id="joegibbs" class="teamList">
<TABLE>
<TR><TD><A NAME="joegibbs"><B>Joe Gibbs Good Head</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;104.84</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>36 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;36.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>9 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;38.70</TD></TR>
<TR class="account1"><TD>1 Tie</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;2.15</TD></TR>
<TR class="account2"><TD>Division Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;12.08</TD></TR>
<TR class="account1"><TD>Playoff Team</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;72.32</TD></TR>
<TR class="account2"><TD>Championship Game</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;108.48</TD></TR>
<TR class="account1"><TD>League Champion</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;180.81</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;111.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;519.38</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="whiskeytango" class="teamList">
<TABLE>
<TR><TD><A NAME="whiskeytango"><B>Whiskey Tango</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;288.82</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>20 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;20.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>3 Illegal Lineup</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;&nbsp;3.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>5 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;21.50</TD></TR>
<TR class="account2"><TD>2 Ties</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;4.30</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;&nbsp;98.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;314.62</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<div id="woodland" class="teamList">
<TABLE>
<TR><TD><A NAME="woodland"><B>Woodland Rangers</B></A></TD><TD ALIGN=Right><B>Debits</B></TD><TD ALIGN=Right><B>Credits</B></TD></TR>
<TR class="account1"><TD>Last Year's Balance</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;32.34</TD></TR>
<TR class="account2"><TD>Entry Fee</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;75.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>Late Fees</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;11.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account2"><TD>21 Extra Transactions</TD><TD ALIGN=Right><FONT COLOR="#FF0000">$&nbsp;&nbsp;21.00</FONT></TD><TD ALIGN=Right>&nbsp;</TD></TR>
<TR class="account1"><TD>4 Wins</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;17.20</TD></TR>
<TR class="account2"><TD>1 Tie</TD><TD ALIGN=Right><FONT COLOR="#FF0000">&nbsp;</FONT></TD><TD ALIGN=Right>$&nbsp;&nbsp;&nbsp;2.15</TD></TR>
<TR BGCOLOR="#000000"><TD>&nbsp;</TD><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>
<TR class="summary"><TD><B>Totals</B></TD><TD ALIGN=Right><B><FONT COLOR="#FF0000">$&nbsp;107.00</FONT></B></TD><TD ALIGN=Right><B>$&nbsp;&nbsp;51.69</B></TD></TR>
<tr><td></td></tr>
</table>
</div>

<? include "base/footer.php"; ?>
