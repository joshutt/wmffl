<?
require_once "utils/start.php";

$title = "2009 WMFFL Financial Statements";

include "base/menu.php";
?>

<H1 ALIGN=Center>League Finances</H1>
<H5 ALIGN=Center>Last Updated 1/1/2010</H5>
<HR size = "1">

<P>This is a complete breakdown of the league finance.  What we
take in from entry fees, transactions, invalid lineups, etc. and
what we spend on software, trophy, payouts etc.  Included is a
chart detailing how much will be paid out for each event.  There is
also a page detailing each team's balance.</P>

<CENTER>
<A HREF="#income">League Income</A>&nbsp;&nbsp;&nbsp;
<A HREF="#expenses">League Expenses</A>&nbsp;&nbsp;&nbsp;
<A HREF="#payout">Financial Payouts</A>&nbsp;&nbsp;&nbsp;
<A HREF="teammoney.php">Team Accounts</A>
</CENTER>

<HR size = "1">

<TABLE WIDTH=100%>
<TR><TD><A NAME="income"><B>INCOME</B></A></TD></TR>
<TR><TD>10 Teams - Entry fees @ $75</TD><TD>$&nbsp;750.00</TD></TR>
<TR><TD>206 Extra Transacions @ $1</TD><TD>$&nbsp;206.00</TD></TR>
<tr><td>4 One Day late fee @ $1</td><td>$&nbsp;&nbsp;&nbsp;4.00</td></tr>
<tr><td>5 One month late fee @ $5</td><td>$&nbsp;&nbsp;25.00</td></tr>
<TR><TD>8 Bad Lineups @ $1</TD><TD>$&nbsp;&nbsp;&nbsp;8.00</TD></TR>
<TR><TD><B>Total Income</B></TD><TD><B>$&nbsp;993.00</B></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD COLSPAN=2><HR size = "1"></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD><A NAME="expenses"><B>EXPENSES</B></A></TD></TR>
<TR><TD>Trophy Plate</TD><TD>$&nbsp;&nbsp;&nbsp;5.00</TD></TR>
<TR><TD>Web hosting fee</TD><TD>$&nbsp;&nbsp;90.00</TD></TR>
<TR><TD>Shipping</TD><TD>$&nbsp;&nbsp;61.35</TD></TR>
<TR><TD><B>Total Expenses</B></TD><TD><B>$&nbsp;156.35</B></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD COLSPAN=2><HR size = "1"></TD></TR>
</TABLE>

<TABLE WIDTH=100%>
<TR><TD><A NAME="payout"><B>PAYOUTS</B></A></TD></TR>
<TR><TD>League Expenses</TD><TD>$&nbsp;171.98</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Expenses</TD><TD>&nbsp;</TD><TD>$&nbsp;156.35</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Max 10% Buffer</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;15.63</TD></TR>
<TR><TD>Regular Season Wins</TD><TD>$&nbsp;273.67</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Each Win</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;&nbsp;3.91</TD></TR>
<TR><TD>Playoff Rewards</TD><TD>$&nbsp;547.34</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Division Winners</TD><TD>&nbsp;</TD><TD>2 @ $13.68</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Playoff Teams</TD><TD>&nbsp;</TD><TD>4 @ $54.73</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Championship Game</TD><TD>&nbsp;</TD><TD>2 @ $82.10</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;League Champion</TD><TD>&nbsp;</TD><TD>1 @ $136.84</TD></TR>
</TABLE>

<? include "base/footer.php"; ?>
