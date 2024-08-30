<?php
/**
 * @var $conn mysqli
 * @var $teamnum int
 */
$PASS_THRES = .67;
$FAIL_THRES = .51;

	// Include the file that defines the connection information
// establish connection
//require "base/conn.php";
//require "login/loginglob.php";
//
//if (!$isin) {
//	header("Location: /rules/ballot.php");
//}

foreach ($_POST as $key => $value) {
    if ($key === 'submit') continue;
	$thequery = "update ballot set vote='$value' where issueid='$key' and teamid=$teamnum";
    mysqli_query($conn, $thequery);

	$checkpassfail = "select sum(if(vote='Accept',1,0))/sum(if(vote<>'Abstain',1,0)) as Pass, sum(if(vote='Reject',1,0))/sum(if(vote<>'Abstain',1,0)) as Reject from ballot where issueid=".$key;
    $result = mysqli_query($conn, $checkpassfail);
    list($pass, $fail) = mysqli_fetch_row($result);
	if ($pass >= $PASS_THRES) {
		// Here we email a pass message
		$body = "Proposal $key has passed";
		mail ("commish@wmffl.com", "Proposal Results", $body, "From: webmaster@wmffl.com");
	} else if ($fail >= $FAIL_THRES) {
		// Here we email a fail message
		$body = "Proposal $key has failed";
		mail ("commish@wmffl.com", "Proposal Results", $body, "From: webmaster@wmffl.com");
	}
	
	$anotherquery = "select IssueNum, IssueName from issues where issueid=$key";
    $result = mysqli_query($conn, $anotherquery);
    list($voteNum[$key], $voteName[$key]) = mysqli_fetch_row($result);
	$voteCast[$key] = $value;
}
// For each key in HTTP_POST_VARS
	// key is issueid, value is result
	// print votes and save to database
	
	// check for passage or rejection if so send email
	
?>

<H1 ALIGN=Center>Votes Cast</H1>
<HR>

<div class="container">
<P>Your casted votes were recieved.  Below is a record of how you voted.
If you would like to change you vote, you may do so at any time before the
ballot closes.  <A HREF="ballot">Ballot</A>.</P>

<P>
    <?php
foreach ($voteNum as $key => $value) {
    if ($key === "submit") {
        continue;
    }
	print '<p>' .$value." - ".$voteName[$key]." - ".$voteCast[$key]."</p>";
}
?>
</P>
</div>

