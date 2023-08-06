<?
require_once "utils/start.php";
if (!$isin) {
    header("Location: tradescreen.php");
    exit;
}

//$teamid = 2;
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

$offerid = $_GET["offerid"];

$they = $_SESSION["they"];
//print_r($they);
$you = $_SESSION["you"];
$theyItems = buildObjectArray($they);
$youItems = buildObjectArray($you);

$teamto = $_SESSION["teamto"];
$otherTeam = loadTeam($teamto);
$myTeam = loadTeam($teamnum);

$title = "Trades";
?>

<? 
//$teamid = 0;
include "base/menu.php"; 
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

<P><B><?print $myTeam->getName();?></B> offer <? print printList($youItems);?><BR>
to the <B><?print $otherTeam->getName();?></B> in exchange for <? print printList($theyItems); ?>
<?
?>
</P>

<FORM ACTION="edittrade.php" METHOD="POST">
<CENTER><INPUT TYPE="submit" NAME="edit" VALUE="Edit Offer">
<?
foreach ($they as $value) {
    print "<input type=\"hidden\" name=\"they[]\" value=\"$value\">";
}
foreach ($you as $value) {
    print "<input type=\"hidden\" name=\"you[]\" value=\"$value\">";
}
?>
<INPUT TYPE="hidden" NAME="offerid" VALUE="<? print $offerid;?>">
<input type="hidden" name="teamto" value="<? print $teamto; ?>"/>
</CENTER>
</FORM>

<FORM ACTION="processconfirm.php" METHOD="POST">
Comments:<BR>
<CENTER>
<input type="hidden" name="offerid" value="<? print $offerid;?>">
<input type="hidden" name="teamto" value="<? print $teamto; ?>"/>
<?
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

<? include "base/footer.php"; ?>
</BODY>
</HTML>
