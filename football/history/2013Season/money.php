<?
require_once "utils/start.php";

$title = "2013 WMFFL Financial Statements";

include "base/menu.php";
?>

<H1 ALIGN=Center>League Finances</H1>
<H5 ALIGN=Center>Last Updated 1/15/2014</H5>
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
<TR><TD>12 Teams - Entry fees @ $75</TD><TD>$&nbsp;900.00</TD></TR>
<TR><TD>233 Extra Transactions @ $1</TD><TD>$&nbsp;233.00</TD></TR>
<TR><TD>12 Bad Lineups @ $1</TD><TD>$&nbsp;&nbsp;12.00</TD></TR>
<TR><TD>2 One Day Fee @ $1</TD><TD>$&nbsp;&nbsp;&nbsp;2.00</TD></TR>
<TR><TD>2 Month Fee @ $5</TD><TD>$&nbsp;&nbsp;10.00</TD></TR>
<TR><TD><B>Total Income</B></TD><TD><B>$1157.00</B></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD COLSPAN=2><HR size = "1"></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD><A NAME="expenses"><B>EXPENSES</B></A></TD></TR>
<TR><TD>Trophy Plate</TD><TD>$&nbsp;&nbsp;&nbsp;6.00</TD></TR>
<TR><TD>Web hosting fee</TD><TD>$&nbsp;&nbsp;90.00</TD></TR>
<TR><TD>Paypal Fee</TD><TD>$&nbsp;&nbsp;&nbsp;3.20</TD></TR>
<TR><TD><B>Total Expenses</B></TD><TD><B>$&nbsp;&nbsp;99.20</B></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD COLSPAN=2><HR size = "1"></TD></TR>
</TABLE>

<TABLE WIDTH=100%>
<TR><TD><A NAME="payout"><B>PAYOUTS</B></A></TD></TR>
<TR><TD>League Expenses</TD><TD>$&nbsp;109.12</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Expenses</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;99.20</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Max 10% Buffer</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;&nbsp;9.92</TD></TR>
<TR><TD>Regular Season Wins</TD><TD>$&nbsp;291.20</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Each Win</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;&nbsp;4.16</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Each Tie</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;&nbsp;2.08</TD></TR>
<TR><TD>Playoff Rewards</TD><TD>$&nbsp;698.51</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Division Winners</TD><TD>&nbsp;</TD><TD>3 @ $11.66</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Playoff Teams</TD><TD>&nbsp;</TD><TD>4 @ $69.85</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Championship Game</TD><TD>&nbsp;</TD><TD>2 @ $104.77</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;League Champion</TD><TD>&nbsp;</TD><TD>1 @ $174.61</TD></TR>
</TABLE>

<? include "base/footer.php"; ?>
