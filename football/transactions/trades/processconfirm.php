<?
require_once "utils/start.php";

if (!$isin) {
    header("Location: tradescreen.php");
    exit;
}

require_once "loadTrades.inc.php";

function buildObjectArray($they) {
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

if (isset($_POST["cancel"])) {
	header("Location: tradescreen.php");
    exit();
}

$offerid = $_POST["offerid"];

$they = $_SESSION["they"];
//print_r($they);
$you = $_SESSION["you"];
$theyItems = buildObjectArray($they);
$youItems = buildObjectArray($you);

$teamto = $_SESSION["teamto"];
$otherTeam = loadTeam($teamto);
$myTeam = loadTeam($teamnum);

// Update database
$theTrade = new Trade($offerid);
if (isset($offerid) && $offerid <> 0) {
    $theTrade->setID($offerid);
}
$theTrade->setOtherTeam($otherTeam);
$theTrade->setThisTeam($myTeam);
$toPicks = array();
$toPlay = array();
$toPts = array();
$fromPicks = array();
$fromPlay = array();
$fromPts = array();
foreach ($theyItems as $theyO) {
    $objType = get_class($theyO);
    if (strtolower($objType) == "player") {
        array_push($toPlay, $theyO);
    } else if (strtolower($objType) == "points") {
        array_push($toPts, $theyO);
    } else {
        array_push($toPicks, $theyO);
    }
}
foreach ($youItems as $theyO) {
    $objType = get_class($theyO);
    if (strtolower($objType) == "player") {
        array_push($fromPlay, $theyO);
    } else if (strtolower($objType) == "points") {
        array_push($fromPts, $theyO);
    } else {
        array_push($fromPicks, $theyO);
    }
}
$theTrade->setPlayersTo($toPlay);
$theTrade->setPlayersFrom($fromPlay);
$theTrade->setPicksTo($toPicks);
$theTrade->setPicksFrom($fromPicks);
$theTrade->setPointsTo($toPts);
$theTrade->setPointsFrom($fromPts);
saveOffer($theTrade);

// Create mailmessage
$mailmessage = "You have been offered a trade: \n\n";
$mailmessage .= $myTeam->getName()." offer ".printList($youItems);
$mailmessage .= " to the ".$otherTeam->getName()." in exchange for ";
$mailmessage .= printList($theyItems);
$mailmessage .= "\n\n";
$mailmessage .= $_POST["comments"];
$mailmessage .= "\n\n";
$mailmessage .= "To accept, reject or modify this trade please go to the trade page: http://wmffl.com/transactions/trades/tradescreen.php  ";
$mailmessage .= "This offer will expire in 7 days.";

$subject = "Trade Offer";


// Send email
$addyGet = "SELECT email, teamid FROM user WHERE teamid in ($teamnum, $teamto) AND active='Y'";
$addyResults = mysql_query($addyGet);
$first = true;
$replyFirst = true;
$address = "";
$replyTo = "Reply-To: ";
while (list($emailAdd, $fromTeam) = mysql_fetch_array($addyResults)) {
    if (!$first) {
        $address .= ", ";
    }
    $address .= $emailAdd;
    $first = false;
    if ($fromTeam == $teamnum) {
        if (!$replyFirst) {
            $replyTo .= ", ";
        }
        $replyTo .= $emailAdd;
        $replyFirst = false;
    }
}
mail($address, $subject, $mailmessage, "From: webmaster@wmffl.com\r\n$replyTo");

$title = "Trades";
include "base/menu.php"; 
?>

<H1 ALIGN="Center">Offer Submitted</H1>
<HR>

<P>The following offer terms have been submitted:</P>

<P><B><?print $myTeam->getName();?></B> offer <? print printList($youItems);?><BR>
to the <B><?print $otherTeam->getName();?></B> in exchange for <? print printList($theyItems); ?>

<P>This trade will become offical when the other team agrees to these terms.  
Either team may amend or withdraw this offer at any time prior to its approval.
The offer will automaticlly become void in  seven days if no further action is 
taken by either party.</P>

<P>Return to <A HREF="tradescreen.php">trade screen</A></P>

<? include "base/footer.html"; ?>
</BODY>
</HTML>
