<?
require_once "utils/start.php";

$title = "WMFFL Rule Proposals";
include "base/menu.php";

?>

<H1 ALIGN=Center>Rule Proposals</H1>
<HR size = "1">

<?
if ($isin) {
?>
<P>This form is provided to allow for year round rule proposals.
If at any point you have a rule that you would like to propose
feel free to add it to the ballot.  The current ballot can be
found <A HREF="ballot.php">here</A>.</P>

<FORM ACTION="propose.php" METHOD="post">
<TABLE>
<TR><TD><B>Name:</B></TD><TD><INPUT TYPE="Text" NAME="Name" SIZE=40></TD></TR>
<TR><TD><B>Team:</B></TD><TD><INPUT TYPE="Text" NAME="Team" SIZE=40></TD></TR>
<TR><TD><B>Email:</B></TD><TD><INPUT TYPE="Text" NAME="email" SIZE=40></TD></TR>
<TR><TD><B>Proposal:</B></TD><TD><TEXTAREA NAME="proposal" COLS=50 ROWS=5 WRAP=Virtual></TEXTAREA></TD></TR>
</TABLE>
<INPUT TYPE="Submit" VALUE="Send Proposal">
</FORM>

<?
} else {
?>
You must be logged in to submit rule proposals.
<?
}

include "base/footer.php";
?>
