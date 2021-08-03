<?
require_once "utils/start.php";

$title = "2012 WMFFL Financial Statements";

include "base/menu.php";
?>

<H1 ALIGN=Center>League Finances</H1>
<H5 ALIGN=Center>Last Updated 1/26/2013</H5>
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
<TR><TD>229 Extra Transactions @ $1</TD><TD>$&nbsp;229.00</TD></TR>
<TR><TD>5 Bad Lineups @ $1</TD><TD>$&nbsp;&nbsp;&nbsp;5.00</TD></TR>
<TR><TD>2 One Day Fee @ $1</TD><TD>$&nbsp;&nbsp;&nbsp;2.00</TD></TR>
<TR><TD>3 Month Fee @ $5</TD><TD>$&nbsp;&nbsp;15.00</TD></TR>
<TR><TD><B>Total Income</B></TD><TD><B>$1151.00</B></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD COLSPAN=2><HR size = "1"></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD><A NAME="expenses"><B>EXPENSES</B></A></TD></TR>
<TR><TD>Trophy Plate</TD><TD>$&nbsp;&nbsp;&nbsp;6.00</TD></TR>
<TR><TD>Web hosting fee</TD><TD>$&nbsp;&nbsp;90.00</TD></TR>
<TR><TD>Domain Registration</TD><TD>$&nbsp;&nbsp;36.94</TD></TR>
<TR><TD>Paypal Fee</TD><TD>$&nbsp;&nbsp;&nbsp;2.56</TD></TR>
<TR><TD><B>Total Expenses</B></TD><TD><B>$&nbsp;135.50</B></TD></TR>
<TR><TD></TD><TD></TD></TR>
<TR><TD COLSPAN=2><HR size = "1"></TD></TR>
</TABLE>

<TABLE WIDTH=100%>
<TR><TD><A NAME="payout"><B>PAYOUTS</B></A></TD></TR>
<TR><TD>League Expenses</TD><TD>$&nbsp;149.05</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Expenses</TD><TD>&nbsp;</TD><TD>$&nbsp;135.50</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Max 10% Buffer</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;13.55</TD></TR>
<TR><TD>Regular Season Wins</TD><TD>$&nbsp;334.32</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Each Win</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;&nbsp;3.98</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Each Tie</TD><TD>&nbsp;</TD><TD>$&nbsp;&nbsp;&nbsp;1.89</TD></TR>
<TR><TD>Playoff Rewards</TD><TD>$&nbsp;667.70</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Division Winners</TD><TD>&nbsp;</TD><TD>3 @ $11.15</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Playoff Teams</TD><TD>&nbsp;</TD><TD>4 @ $66.76</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;Championship Game</TD><TD>&nbsp;</TD><TD>2 @ $100.14</TD></TR>
<TR BGCOLOR="#CCCCCC"><TD>&nbsp;&nbsp;&nbsp;League Champion</TD><TD>&nbsp;</TD><TD>1 @ $166.91</TD></TR>
</TABLE>

<? include "base/footer.php"; ?>
