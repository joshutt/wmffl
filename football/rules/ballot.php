<?
require_once "utils/start.php";

$title = "WMFFL Ballot";

include "base/menu.php";
?>

<H1 ALIGN=Center>Ballot</H1>
<HR size = "1">

<?
if ($isin) {
?>

<P>
For each of the ballot items below your current vote, please select your vote, 
then press the "VOTE" button to have you vote counted.  To review the issues in question you may go to the <A HREF="/rules/proposals2016.php">proposals page</A>
</P>

<TABLE>
<FORM ACTION="ballotcount.php" METHOD=POST>
<?

	$thequery = "select i.issueid, i.issuenum, i.issuename, b.vote, i.description
				from issues i, ballot b
				where i.issueid=b.issueid
				and i.startDate<=now() 
				and (Deadline is null or Deadline >= now())
				and b.teamid=".$teamnum." order by issuenum";
	
	$results = mysql_query($thequery);
	while (list($issueid, $issuenum, $issuename, $vote, $descr) = mysql_fetch_row($results)) {

        $accept = "Accept";
        $reject = "Reject";
        $abstain = "Abstain";
        $votelabel = $vote;
        if ($issueid == 84) {
            $accept = "10 Teams";
            $reject = "12 Teams";
            $abstain = "No Preference";
            switch ($vote) {
                case "Accept" : $votelabel=$accept; break;
                case "Reject" : $votelabel=$reject; break;
                case "Abstain" : $votelabel=$abstain; break;
            }
        }
?>	
	<TR><TH COLSPAN=3 ALIGN="Left">
		<? print $issuenum;?> - <? print $issuename;?>
	</TH></TR>
	<TR><TD COLSPAN=3><? print $descr; ?></TD></TR>
	<TR><TD></TD><TD COLSPAN=2><I>
	<? if ($vote!="") {
			print "Your current vote is to $votelabel this proposal";
			//if ($vote == "1") print "approve this proposal";
			//else print "reject this proposal";
		} else {
			print "You have not voted on this proposal";
		}
	?>
	</I></TD></TR>
	<TR><TD></TD><TD>
	<INPUT TYPE="radio" NAME="<? print $issueid;?>" VALUE="Accept" <? if ($vote=="Accept") print "CHECKED";?>>
	</TD><TD><? print $accept; ?></TD></TR>
	<TR><TD></TD><TD>
		<INPUT TYPE="radio" NAME="<? print $issueid;?>" VALUE="Reject" <? if ($vote=="Reject") print "CHECKED";?>>
	</TD><TD><? print $reject; ?></TD></TR>
	<TR><TD></TD><TD>
		<INPUT TYPE="radio" NAME="<? print $issueid;?>" VALUE="Abstain" <? if ($vote=="Abstain") print "CHECKED";?>>
	</TD><TD><? print $abstain; ?></TD></TR>
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

<CENTER><B>You must be logged in to cast your votes </B></CENTER>

<? }	include "base/footer.html";
?>

