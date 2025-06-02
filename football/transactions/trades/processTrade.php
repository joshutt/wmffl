<?php
/**
 * @var $isin boolean
 * @var $conn mysqli
 * @var $teamnum int
 */
require_once 'utils/start.php';
include_once 'trade.class.php';
include_once 'loadTrades.inc.php';

if (!$isin) {
    header('Location: tradescreen.php');
    exit;
}
//$teamid = 2;
$tradeID = $_POST['offerid'];
$action = $_POST['action'];

#print $tradeID;
#print $action;

if ($action == 'Amend' || $action == 'Counter') {
    include 'edittrade.php';
    exit(0);
}

$question = '';
switch($action) {
	case 'Accept':
		$question = 'Are you sure you would like to accept this offer?';
		break;
	case 'Reject':
		$question = 'Are you sure you would like to reject this offer?';
		break;
	case 'Withdraw':
		$question = 'Are you sure you would like to withdraw this offer?';
		break;
}

$thisTeam = loadTeam($conn, $teamnum);
$trade = loadTradeByID($conn, $tradeID, $thisTeam);

$thisTeamString = '<B>' .$thisTeam->getName(). '</B> receive ';
$totalCount = sizeof($trade->getPlayersTo()) + sizeof($trade->getPicksTo()) + sizeof($trade->getPointsTo());
$count = 0;
foreach ($trade->getPlayersTo() as $player) {
    $count++;
    if ($count == $totalCount && $count != 1) {
        $thisTeamString .= ' and ';
    } else if ($count > 1) {
        $thisTeamString .= ', ';
    }
    $thisTeamString .= $player->getName(). ' (' .$player->getPos(). '-' .$player->getNFLTeam(). ') ';
}
foreach ($trade->getPicksTo() as $pick) {
    $count++;
    if ($count == $totalCount && $count != 1) {
        $thisTeamString .= ' and ';
    } else if ($count > 1) {
        $thisTeamString .= ', ';
    }
    $thisTeamString .= 'a ' .$pick->getRound().OrdinalEnding($pick->getRound());
    $thisTeamString .= ' round pick in ' .$pick->getSeason();
}
foreach ($trade->getPointsTo() as $point) {
    $count++;
    if ($count == $totalCount && $count != 1) {
        $thisTeamString .= ' and ';
    } else if ($count > 1) {
        $thisTeamString .= ', ';
    }
    $thisTeamString .= $point->getPts(). ' Transaction Points in ' .$point->getSeason();
}
$otherTeam = $trade->getOtherTeam();
$otherTeamString = '<B>' .$otherTeam->getName(). '</B> receive ';
$totalCount = sizeof($trade->getPlayersFrom()) + sizeof($trade->getPicksFrom()) + sizeof($trade->getPointsFrom());
$count = 0;
foreach ($trade->getPlayersFrom() as $player) {
    $count++;
    if ($count == $totalCount && $count != 1) {
        $otherTeamString .= ' and ';
    } else if ($count > 1) {
        $otherTeamString .= ', ';
    }
    $otherTeamString .= $player->getName(). ' (' .$player->getPos(). '-' .$player->getNFLTeam(). ') ';
}
foreach ($trade->getPicksFrom() as $pick) {
    $count++;
    if ($count == $totalCount && $count != 1) {
        $otherTeamString .= ' and ';
    } else if ($count > 1) {
        $otherTeamString .= ', ';
    }
    $otherTeamString .= 'a ' .$pick->getRound().OrdinalEnding($pick->getRound());
    $otherTeamString .= ' round pick in ' .$pick->getSeason();
}
foreach ($trade->getPointsFrom() as $point) {
    $count++;
    if ($count == $totalCount && $count != 1) {
        $otherTeamString .= ' and ';
    } else if ($count > 1) {
        $otherTeamString .= ', ';
    }
    $otherTeamString .= $point->getPts(). ' Transaction Points in ' .$point->getSeason();
}
?>

<HTML>
<HEAD>
<TITLE>Trades</TITLE>
</HEAD>

<?php
//$teamid = 0;
include 'base/menu.php'; ?>

<H1 ALIGN=Center><?= $action;?> Trade Offer</H1>
<HR>

<CENTER>
<FORM ACTION="finalprocess.php" METHOD="POST">
    <INPUT TYPE="hidden" name="action" value="<?= $action ?>">
    <INPUT TYPE="hidden" name="offerid" value="<?= $tradeID ?>">
    <H3 ALIGN=Center><?= $question ?></H3>
    <?= $thisTeamString ?><BR>
    <?= $otherTeamString ?><BR>
<P>Additional Comments:<BR>
<TEXTAREA NAME="comments" COLS=60 ROWS=8>
</TEXTAREA></P>
<TABLE WIDTH=100%><TR><TD WIDTH=25%></TD>
<TD WIDTH="*">
<INPUT TYPE="Submit" NAME="select" VALUE="Yes">
</TD><TD WIDTH="25%"></TD><TD WIDTH="*">
<INPUT TYPE="Submit" NAME="select" VALUE="No">
</TD><TD WIDTH="25%"></TD></TR>
</TABLE>
</FORM>
</CENTER>

<?php include 'base/footer.php'; ?>
</BODY>
</HTML>
