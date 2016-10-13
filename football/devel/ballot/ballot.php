
<HTML>
<HEAD>
<TITLE>WMFFL Ballot</TITLE>
</HEAD>

<?
	// Include the file that defines the connection information
// establish connection
require $DOCUMENT_ROOT."/base/conn.php";
require $DOCUMENT_ROOT."/login/loginglob.php";
	
	include "$DOCUMENT_ROOT/base/menu.php";
?>

<H1 ALIGN=Center>Ballot</H1>
<HR size = "1">

<?
if ($isin) {
?>

<P>
For each of the ballot items below your current vote, please select your vote, 
then press the "VOTE" button to have you vote counted.  To review the issues in question you may go to the <A HREF="/rules/proposals2002.shtml">proposals page</A>
</P>

<TABLE>
<FORM ACTION="ballotcount.php" METHOD=POST>
<?

	$thequery = "select i.issueid, i.issuenum, i.issuename, b.vote
				from issues i, ballot b
				where i.issueid=b.issueid
				and i.startDate<=now() 
				and (Deadline is null or Deadline >= now())
				and b.teamid=".$teamnum." order by issuenum";
	
	$results = mysql_query($thequery);
	while (list($issueid, $issuenum, $issuename, $vote) = mysql_fetch_row($results)) {
?>	
	<TR><TH COLSPAN=3 ALIGN="Left">
		<? print $issuenum;?> - <? print $issuename;?>
	</TH></TR>
	<TR><TD></TD><TD COLSPAN=2>
	<? if ($vote!="") {
			print "Your current vote is to $vote this proposal";
			//if ($vote == "1") print "approve this proposal";
			//else print "reject this proposal";
		} else {
			print "You have not voted on this proposal";
		}
	?>
	</TD></TR>
	<TR><TD></TD><TD>
	<INPUT TYPE="radio" NAME="<? print $issueid;?>" VALUE="Accept" <? if ($vote=="Accept") print "CHECKED";?>>
	</TD><TD>Approve</TD></TR>
	<TR><TD></TD><TD>
		<INPUT TYPE="radio" NAME="<? print $issueid;?>" VALUE="Reject" <? if ($vote=="Reject") print "CHECKED";?>>
	</TD><TD>Reject</TD></TR>
	<TR><TD></TD><TD>
		<INPUT TYPE="radio" NAME="<? print $issueid;?>" VALUE="Abstain" <? if ($vote=="Abstain") print "CHECKED";?>>
	</TD><TD>Abstain</TD></TR>
	<TR><TD>&nbsp;</TD></TR>			

<?
	}
	
?>
<TR><TD COLSPAN=3 ALIGN=Center><INPUT TYPE="SUBMIT" VALUE="VOTE"><INPUT TYPE="RESET"></TD></TR>
</FORM>
</TABLE>

<?
} else {
?>

<CENTER><B>You must be logged in to perform transactions</B></CENTER>

<? }	include "$DOCUMENT_ROOT/base/footer.html";
?>

