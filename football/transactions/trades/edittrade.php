<?
function convertNiceNames(&$_POST, $dir)
{
    foreach ($_POST[$dir] as $key => $ch) {
        if (substr($ch, 0, 5) == "draft") {
            $drID = substr($ch, 5, 1);
            $trYear = $_POST[$dir . "draftyear" . $drID];
            $trRnd = $_POST[$dir . "draftround" . $drID];
            $_POST[$dir][$key] = "pick$trYear$trRnd";
//            array_push($returnList, "pick$trYear$trRnd");
        } else if (substr($ch, 0, 7) == "newprot") {
            $drID = substr($ch, 7, 1);
            $trPts = $_POST[$dir . $ch];
            $trYear = $_POST[$dir . "protyear" . $drID];
            $_POST[$dir][$key] = "pts$trYear$trPts";
        }
    }
}

session_start();
require_once "utils/start.php";

if (!$isin) {
    header("Location: tradescreen.php");
    exit;
}

$NUM_DRAFT_DISPLAY=2;
$NUM_DRAFT_YEARS=5;
$NUM_DRAFT_ROUNDS=16;
$NUM_PTS_DISPLAY=1;

//$teamid = 2;
$updateFlag = false;
if (isset($_POST["cancel"])) {
	header("Location: tradescreen.php");
}

require_once "checkambigous.inc.php";

$badTransArray = array();
$badDraftArray = array();
$ambigous = false;
if (isset($_POST["they"]) && isset($_POST["you"])) {
    convertNiceNames($_POST, "they");
    convertNiceNames($_POST, "you");

    //print "ambigous $ambigous<br/>";
    $ambigous = checkTransactions($teamnum, $badTransArray, $_POST);
    //print "Check Draft, ambigous $ambigous<br/>";
    $ambigous = checkDraft($teamnum, $badDraftArray, $_POST) || $ambigous;
    //print "Post Check Draft, ambigous $ambigous<br/>";
}

if (!$ambigous && isset($_POST["confirm"])) {
    //session_start();
    //$_SESSION["ab"] = "AlphaBeta";
    $_SESSION["they"] = $_POST["they"];
    //print_r($_SESSION["they"]);
    $_SESSION["you"] = $_POST["you"];
    $_SESSION["teamto"] = $_POST["teamto"];
    $offerid = $_POST["offerid"];
	//header("Location: ambigouspick.php?offerid=$offerid");
	header("Location: confirmoffer.php?offerid=$offerid");
} elseif ($ambigous || isset($_POST["update"]) || isset($_POST["edit"])) {
    $updateFlag = true;
}

include_once "trade.class.php";
include_once "loadTrades.inc.php";
include_once "base/useful.php";

//$currentSeason =2003;
$thisTeam = loadTeam($teamnum);

if (isset($offerid) && $offerid != 0) {
    $trade = loadTradeByID($offerid, $thisTeam);

    $otherTeam = $trade->getOtherTeam();
} else {
    $offerid=0;
    $trade = new Trade($offerid);
    if (isset($_POST["teamto"])) {
        $otherTeamID = $_POST["teamto"];
    } else {
        $otherTeamID = $trade;
    }
    $otherTeam = loadTeam($otherTeamID);
    $trade->setOtherTeam($otherTeam);
}
$mapping = array($teamnum=>$thisTeam->getName(), 
                $otherTeam->getID()=>$otherTeam->getName());

//print_r ($_POST);
//print "<br/>";

if ($updateFlag) {
    $you = $_POST["you"];
    $they = $_POST["they"];
    include "updatetrade.inc.php";
//    print_r ($trade);
}

//session_start();
//session_register("trade");

?>


<HTML>
<HEAD>
<TITLE>Trades</TITLE>
</HEAD>

<? 
//$teamid = 0;
include "base/menu.php"; 
?>

<H1 ALIGN=Center>Edit Offer</H1>
<HR>

<P>Below is a list of the current participants in this trade.  To add a player
not already in the trade check the box next to their name.  To add a draft pick
check the draft pick box and select what year and round the draft pick is for.
To add a transaction/protection points to the trade, check the box and enter the
number of transaction points to give up. To remove anything from the trade, 
uncheck the approprate box.  </P>

<P>When done making changes press "Update Offer" to refresh this screen with
your new terms.  This option does NOT modify the offer it only refreshes this
screen to allow you to continue making changes to the offer.  Press "Confirm 
Changes" if you are ready to submit your offer to the other team.  If you 
change your mind about amending the trade, select "Cancel".  This option will
not withdraw the trade, only cancel the changes you have made to it.</P>

<FORM ACTION="edittrade.php" METHOD="POST">
<INPUT TYPE="hidden" NAME="offerid" VALUE="<? print $offerid; ?>"/>

<? include "ambigouserrors.inc.php"; ?>

<TABLE WIDTH=100%>
<TR><TD COLSPAN=2><H3 ALIGN="Center">Trade So Far</H3></TD></TR>
<TR><TD></TD></TR>

<TR><TD VALIGN="top"><B><? print $thisTeam->getName(); ?> Give Up:</B><BR>
<?
$playersFrom = $trade->getPlayersFrom();
foreach ($playersFrom as $player) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"you[]\" VALUE=\"play".$player->getID()."\" CHECKED>";
    print $player->getName()." (".$player->getPos()."-".$player->getNFLTeam().")<BR>";
}
foreach ($trade->getPicksFrom() as $pick) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"you[]\" VALUE=\"pick".$pick->getSeason().$pick->getRound()."\" CHECKED>";
    print $pick->getSeason()."-".$pick->getRound().OrdinalEnding($pick->getRound())." Round Pick<BR>";
}
foreach ($trade->getPointsFrom() as $pts) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"you[]\" VALUE=\"pts".$pts->getSeason().$pts->getPts()."\" CHECKED>";
    print $pts->getPts()." transaction points in ".$pts->getSeason()."<BR>";
}
?>
</TD>

<TD VALIGN="top"><B><? print $otherTeam->getName();?> Give Up:</B><BR>
<?
$playersTo = $trade->getPlayersTo();
foreach ($playersTo as $player) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"they[]\" VALUE=\"play".$player->getID()."\" CHECKED>";
    print $player->getName()." (".$player->getPos()."-".$player->getNFLTeam().")<BR>";
}
foreach ($trade->getPicksTo() as $pick) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"they[]\" VALUE=\"pick".$pick->getSeason().$pick->getRound()."\" CHECKED>";
    print $pick->getSeason()."-".$pick->getRound().OrdinalEnding($pick->getRound())." Round Pick<BR>";
}
foreach ($trade->getPointsTo() as $pts) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"they[]\" VALUE=\"pts".$pts->getSeason().$pts->getPts()."\" CHECKED>";
    print $pts->getPts()." transaction points in ".$pts->getSeason()."<BR>";
}
?>
</TD></TR>


<TR><TD>&nbsp;</TD></TR>
<TR><TD COLSPAN=2><H3 ALIGN="Center">Add to Trade</H3></TD></TR>
<TR><TD></TD></TR>


<TR><TD VALIGN="top"><B>Your Roster:</B><BR>
<?
$roster = loadRoster($thisTeam);
foreach ($roster as $player) {
    if ($player->getPos() == "HC") {
        continue;
    }
    print "<INPUT TYPE=\"checkbox\" NAME=\"you[]\" VALUE=\"play".$player->getID()."\">";
    print $player->getName()." (".$player->getPos()."-".$player->getNFLTeam().")<BR>";
}
?>
</TD>

<TD VALIGN="top"><B>Their Roster:</B><BR>
<?
$roster = loadRoster($otherTeam);
foreach ($roster as $player) {
    if ($player->getPos() == "HC") {
        continue;
    }
    print "<INPUT TYPE=\"checkbox\" NAME=\"they[]\" VALUE=\"play".$player->getID()."\">";
    print $player->getName()." (".$player->getPos()."-".$player->getNFLTeam().")<BR>";
}
?>
</TD></TR>

<TR><TD VALIGN="top">

<?
for ($i=1; $i<=$NUM_DRAFT_DISPLAY; $i++) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"you[]\" VALUE=\"draft$i\">Draft Pick:";
    print "<SELECT NAME=\"youdraftyear$i\">";
    for ($j=1; $j<=$NUM_DRAFT_YEARS; $j++) {
        $season = $currentSeason+$j;
        if ($currentWeek == 0) {
            $season--;
        }
        print "<OPTION VALUE=\"$season\">$season</OPTION>";
    }
    print "</SELECT>";
    print "<SELECT NAME=\"youdraftround$i\">";
    for ($j=1; $j<=$NUM_DRAFT_ROUNDS; $j++) {
        print "<OPTION VALUE=\"$j\">$j</OPTION>";
    }
    print "</SELECT><BR>";
}

for ($i=1; $i<=$NUM_PTS_DISPLAY; $i++) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"you[]\" VALUE=\"newprot$i\">Trans Points:";
    print "<INPUT TYPE=\"text\" NAME=\"younewprot$i\" VALUE=\"0\" SIZE=\"3\" MAXLENGTH=\"2\">";
    print "<SELECT NAME=\"youprotyear$i\">";
    for ($j=0; $j<=$NUM_DRAFT_YEARS; $j++) {
        $season = $currentSeason+$j;
        print "<OPTION VALUE=\"$season\">$season</OPTION>";
    }
    print "</SELECT><BR>";
}
?>
</TD>

<TD VALIGN="top">
<?
for ($i=1; $i<=$NUM_DRAFT_DISPLAY; $i++) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"they[]\" VALUE=\"draft$i\">Draft Pick:";
    print "<SELECT NAME=\"theydraftyear$i\">";
    for ($j=1; $j<=$NUM_DRAFT_YEARS; $j++) {
        $season = $currentSeason+$j;
        if ($currentWeek == 0) {
            $season--;
        }
        print "<OPTION VALUE=\"$season\">$season</OPTION>";
    }
    print "</SELECT>";
    print "<SELECT NAME=\"theydraftround$i\">";
    for ($j=1; $j<=$NUM_DRAFT_ROUNDS; $j++) {
        print "<OPTION VALUE=\"$j\">$j</OPTION>";
    }
    print "</SELECT><BR>";
}

for ($i=1; $i<=$NUM_PTS_DISPLAY; $i++) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"they[]\" VALUE=\"newprot$i\">Trans Points:";
    print "<INPUT TYPE=\"text\" NAME=\"theynewprot$i\" VALUE=\"0\" SIZE=\"3\" MAXLENGTH=\"2\">";
    print "<SELECT NAME=\"theyprotyear$i\">";
    for ($j=0; $j<=$NUM_DRAFT_YEARS; $j++) {
        $season = $currentSeason+$j;
        print "<OPTION VALUE=\"$season\">$season</OPTION>";
    }
    print "</SELECT><BR>";
}
?>

<input type="hidden" name="teamto" value="<?print $otherTeam->getID();?>" />
</TD></TR>
</TABLE>

<TABLE WIDTH="100%">
<TR><TD WIDTH="20%">
</TD><TD WIDTH="20%" ALIGN=Center><INPUT TYPE="submit" NAME="update" VALUE="Update Offer">
</TD><TD WIDTH="20%" ALIGN=Center><INPUT TYPE="submit" NAME="confirm" VALUE="Confirm Changes">
</TD><TD WIDTH="20%" ALIGN=Center><INPUT TYPE="submit" NAME="cancel" VALUE="Cancel">
</TD><TD WIDTH="20%">
</TD></TR>
</TABLE>

</FORM>

<? include "base/footer.html"; ?>
</BODY>
</HTML>
