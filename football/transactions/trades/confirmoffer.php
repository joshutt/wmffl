<?php
/**
 * @var $isin boolean
 * @var $conn mysqli
 * @var $teamnum int
 */
require_once 'utils/start.php';
if (!$isin) {
    header('Location: tradescreen.php');
    exit;
}

//$teamid = 2;
require_once 'loadTrades.inc.php';

function buildObjectArray(mysqli $conn, $they): array
{
    $theyItems = array();
    foreach ($they as $value) {
        if (str_starts_with($value, 'play')) {
            $theyItems[] = loadPlayer($conn, substr($value, 4));
        } else if (str_starts_with($value, 'pick')) {
            $newPick = new Pick(substr($value, 4, 4), substr($value, 8, 2), 0);
            $theyItems[] = $newPick;
        } else if (str_starts_with($value, 'pts')) {
            $newPts = new Points(substr($value, 7, 2), substr($value, 3, 4));
            $theyItems[] = $newPts;
        }
    }
    return $theyItems;
}

$offerid = $_GET['offerid'];

$they = $_SESSION['they'];
//print_r($they);
$you = $_SESSION['you'];
$theyItems = buildObjectArray($conn, $they);
$youItems = buildObjectArray($conn, $you);

$teamto = $_SESSION['teamto'];
$otherTeam = loadTeam($conn, $teamto);
$myTeam = loadTeam($conn, $teamnum);

$title = 'Trades';
?>

<?php
//$teamid = 0;
include 'base/menu.php';
?>

<H1 ALIGN=Center>Confirm Offer</H1>
<HR>

<P>Review the current terms of the offer.  If the offer is still not what you
intended you may select "Edit Offer" and return to editing the offer or 
"Cancel" to discard these changes.  If the trade conditions meet your 
satisfaction you may type some comments to the other owner and select "Make 
Offer".  The offer will then be recorded and an email notifing the other owner 
will be made.  This offer will become offical when accepted by the other team.
Before that time, either you or the other team my amend or reject the trade.
If no action is taken for seven days the offer will automaticlly become void.
</P>

<H3 ALIGN=Center>Current Offer</H3>

<P><B><?php print $myTeam->getName();?></B> offer <?php print printList($youItems);?><BR>
to the <B><?php print $otherTeam->getName();?></B> in exchange for <?php print printList($theyItems); ?>
    <?php
?>
</P>

<FORM ACTION="edittrade.php" METHOD="POST">
<CENTER><INPUT TYPE="submit" NAME="edit" VALUE="Edit Offer">
    <?php
foreach ($they as $value) {
    print "<input type=\"hidden\" name=\"they[]\" value=\"$value\">";
}
foreach ($you as $value) {
    print "<input type=\"hidden\" name=\"you[]\" value=\"$value\">";
}
?>
<INPUT TYPE="hidden" NAME="offerid" VALUE="<?php print $offerid;?>">
<input type="hidden" name="teamto" value="<?php print $teamto; ?>"/>
</CENTER>
</FORM>

<FORM ACTION="processconfirm.php" METHOD="POST">
Comments:<BR>
<CENTER>
<input type="hidden" name="offerid" value="<?php print $offerid;?>">
<input type="hidden" name="teamto" value="<?php print $teamto; ?>"/>
    <?php
foreach ($they as $value) {
    print "<input type=\"hidden\" name=\"they[]\" value=\"$value\">";
}
foreach ($you as $value) {
    print "<input type=\"hidden\" name=\"you[]\" value=\"$value\">";
}
?>
<TEXTAREA NAME="comments" COLS="60" ROWS="8">
</TEXTAREA><BR>
<TABLE ALIGN=Center WIDTH=75%>
<TR><TD ALIGN=Center WIDTH=50%>
<INPUT TYPE="submit" NAME="offer" VALUE="Make Offer">
</TD><TD ALIGN=Center WIDTH=50%>
<INPUT TYPE="submit" NAME="cancel" VALUE="Cancel">
</TD></TR></TABLE>
</CENTER>
</FORM>

<?php include 'base/footer.php'; ?>
</BODY>
</HTML>
