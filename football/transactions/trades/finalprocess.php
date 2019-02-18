<?
require_once "utils/start.php";

require_once "loadTrades.inc.php";
//$teamid = 2;
if (!$isin) {
    header("Location: tradescreen.php");
    exit;
}

function buildObjectArray($trade, $dir) {
    $theyItems = array();
    foreach ($they as $value) {
        if (substr($value, 0, 4) == "play") {
            array_push($theyItems, loadPlayer(substr($value, 4)));
        } else if (substr($value, 0, 4) == "pick") {
            $newPick = new Pick(substr($value, 4, 4), substr($value, 8, 2), 0);
            array_push($theyItems, $newPick);
        } else if (substr($value, 0, 3) == "pts") {
            $newPts = new Points(substr($value, 7, 2), substr($value, 3, 4));
            array_push($theyItems, $newPts);
        }
    }
    return $theyItems;
}

$selection = $_POST["select"];
if ($selection == "Yes") {

//print "Yes";
    $action = $_POST["action"];
    $offerid = $_POST["offerid"];
    $comments = $_POST["comments"];

/*
p
int $action;
print $offerid;
print $comments;
*/

    $teamA = loadTeam($teamnum);
    $trade = loadTradeByID($offerid, $teamA);
//    $teamA = $trade->getThisTeam();
    $teamB = $trade->getOtherTeam();
    $theyItems = array_merge($trade->getPlayersFrom(), $trade->getPicksFrom(), $trade->getPointsFrom());
    $youItems = array_merge($trade->getPlayersTo(), $trade->getPicksTo(), $trade->getPointsTo());

//print_r($theyItems);
//print_r($youItems);

	if ($action == "Withdraw" || $action == "Reject") {
		// mark trade in DB as cancelled
        rejectTrade($offerid);

		// Send an email canceling the trade
        // Create mailmessage
        $mailmessage = "The trade offer between ".$teamA->getName()." and ";
        $mailmessage .= $teamB->getName()." has been cancelled by the ";
        $mailmessage .= $teamA->getName()."\n\n";
        $mailmessage .= $teamA->getName()." send ";
        $mailmessage .= printList($theyItems);
        $mailmessage .= " to the ";
        $mailmessage .= $teamB->getName()." in exchange for ";
        $mailmessage .= printList($youItems);
        $mailmessage .= "\n\nComments: \n";
        $mailmessage .= $_POST["comments"];
        $mailmessage .= "\n\n";
        
        $subject = "Trade Offer Rejected";


        // Send email
        $addyGet = "SELECT email, teamid FROM user WHERE teamid in (".$teamA->getID().", ".$teamB->getID().") AND Active='Y'";
        $addyResults = mysql_query($addyGet);
        $first = true;
        $replyFirst = true;
        $address = "";
        $replyTo = "Reply-To: ";
        while (list($emailAdd, $mailTeam) = mysql_fetch_array($addyResults)) {
            if (!$first) {
                $address .= ", ";
            }
            $address .= $emailAdd;
            $first = false;
            if ($mailTeam == $teamA->getID()) {
                if (!$replyFirst) {
                    $replyTo .= ", ";
                }
                $replyTo .= $emailAdd;
                $replyFirst = false;
            }
        }
        //print $mailmessage;
        mail($address, $subject, $mailmessage, "From: webmaster@wmffl.com\r\n$replyTo");
        
	} else {  // trade must be accepted
    //print "Accept";
        // confirm this is a valid trade
        $isValid = validateTrade($offerid, $teamnum);
    //print "Valid: ".$isValid;
        if (!$isValid) {
            header("Location: tradeinvalid.php?offerid=$offerid");
            exit;
        }
		// send an email accepting the trade

        // Create mailmessage
        $mailmessage = "The trade offer between ".$teamA->getName()." and ";
        $mailmessage .= $teamB->getName()." has been accepted.\n\n";
        $mailmessage .= $teamA->getName()." send ";
        $mailmessage .= printList($theyItems);
        $mailmessage .= " to the ";
        $mailmessage .= $teamB->getName()." in exchange for ";
        $mailmessage .= printList($youItems);
        $mailmessage .= "\n\n";
        $mailmessage .= "Comments: \n";
        $mailmessage .= $_POST["comments"];
        $mailmessage .= "\n\n";
        
        $subject = "Trade Offer Accepted";

        // Send email
        $addyGet = "SELECT email, teamid FROM user WHERE teamid in (".$teamA->getID().", ".$teamB->getID().") AND active='Y'";
        $addyResults = mysql_query($addyGet);
        $first = true;
        $replyFirst = true;
        $address = "";
        $replyTo = "Reply-To: ";
        while (list($emailAdd, $mailTeam) = mysql_fetch_array($addyResults)) {
            if (!$first) {
                $address .= ", ";
            }
            $address .= $emailAdd;
            $first = false;
            if ($mailTeam == $teamA->getID()) {
                if (!$replyFirst) {
                    $replyTo .= ", ";
                } 
                $replyTo .= $emailAdd;
                $replyFirst = false;
            }
        }
        //print $mailmessage;
        mail($address, $subject, $mailmessage, "From: webmaster@wmffl.com\r\n$replyTo");

		// update the database as needed
        acceptTrade($offerid, $teamnum);
	}
}

header("Location: tradescreen.php");
exit();
?>
