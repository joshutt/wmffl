<?php
/**
 * @var $conn mysqli
 */
	// Include the file that defines the connection information
include 'check.inc.php';
// establish connection
require 'base/conn.php';

$thequery = 'select i.issuenum, ';
$thequery .= "sum(if(b.vote='Accept',1,0)), sum(if(b.vote='Reject',1,0)), "; 
$thequery .= "sum(if(b.vote='Abstain',1,0)), sum(if(b.vote='No Vote',1,0)), ";
$thequery .= "sum(if(b.vote='Accept',1,0))/sum(if(b.vote<>'Abstain',1,0)) as Pass, ";
$thequery .= "sum(if(b.vote='Reject',1,0))/sum(if(b.vote<>'Abstain',1,0)) as Reject ";
$thequery .= 'from issues i, ballot b ';
$thequery .= 'where i.issueid=b.issueid and ';
$thequery .= 'i.startDate<=now() and (i.deadline is null or i.deadline>=now()) ';
$thequery .= 'group by i.issuenum';

$result = mysqli_query($conn, $thequery);

print '<P><TABLE BORDER=1><TR><TH>Issue Number</TH><TH>For</TH><TH>For %</TH><TH>Against</TH><TH>Against %</TH><TH>Abstain</TH><TH>No Vote</TH></TR>';

while (list($issuenum, $yes, $no, $maybe, $novote, $pass, $fail) = mysqli_fetch_row($result)) {
    print "<TR><TD>$issuenum</TD><TD>$yes</TD><TD>$pass</TD><TD>$no</TD><TD>$fail</TD><TD>$maybe</TD><TD>$novote</TD></TR>";
}
print '</TABLE></P>';


$thequery = 'select i.issuenum, t.name, b.vote ';
$thequery .= 'from team t, issues i, ballot b ';
$thequery .= 'where b.teamid=t.teamid and i.issueid=b.issueid ';
$thequery .= 'and i.startDate<=now() ';
$thequery .= 'and (i.deadline is null or i.deadline>=now()) ';
$thequery .= 'order by i.issuenum, b.vote, t.name';

$result = mysqli_query($conn, $thequery);

print '<P><TABLE BORDER=1>';

print 'Rows: ' . mysqli_num_rows($result);
$oldissue = '';
while (list($issuenum, $team, $vote) = mysqli_fetch_row($result)) {
    if ($issuenum != $oldissue) {
        print "<TR><TH COLSPAN=2>$issuenum</TH></TR>";
        $oldissue=$issuenum;
    }
    print "<TR><TD>$team &nbsp; </TD><TD>$vote</TD></TR>";
}
print '</TABLE></P>';


