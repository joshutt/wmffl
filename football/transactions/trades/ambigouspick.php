<?
session_start();
require_once "checkambigous.inc.php";
require_once "loadTrades.inc.php";

//$teamid = 2;
$transArray = array();
$ambigous = checkTransactions($teamnum, $transArray);
$ambigous = $ambigous | checkDraft();

if (!$ambigous) {
    header("Location: confirmoffer.php?offerid=$offerid");
}

$they = $_SESSION["they"];
$you = $_SESSION["you"];
$teamto = $_SESSION["teamto"];
$otherTeam = loadTeam($teamto);
$myTeam = loadTeam($teamnum);
$mapping = array($teamto => $otherTeam->getName(), $teamnum => $myTeam->getName());
?>


<HTML>
<HEAD>
<TITLE>Trades</TITLE>
</HEAD>

<? 
include "base/menu.php"; 
?>

<H1 ALIGN=Center>Edit Offer</H1>

<form action="edittrade.php" method="post">
<?
foreach ($they as $value) {
    print "<input type=\"hidden\" name=\"they[]\" value=\"$value\">";
}
foreach ($you as $value) {
    print "<input type=\"hidden\" name=\"you[]\" value=\"$value\">";
}
?>
<input type="hidden" name="offerid" value="<? print $offerid;?>">
<input type="hidden" name="edit" value="1"/>

<HR>

<FONT COLOR="Red">Illegal Draft Pick</FONT><BR>
<P>Current trade involves the Norsemen giving up a 3rd round pick in 2003,
however they do not have a pick in that round.  Please edit the trade to fix
this problem.</P>
<INPUT TYPE="submit" VALUE="Edit Trade">

<HR>

<FONT COLOR="Red">Ambigous Draft Pick</FONT><BR>
<P>The Werewolves have multiple picks in the 1st round of 2004.  Please clarify
which pick to use.</P>
<P><INPUT TYPE="radio" NAME="wer200401" VALUE="1">Pick #3, originally belonging to War Eagles<BR>
<INPUT TYPE="radio" NAME="wer200401" VALUE="2">Pick #6, orginally belong to Werewolves</P>
<INPUT TYPE="submit" VALUE="Edit Trade">


<?
if (count($transArray) > 0) {
?>

<hr/>
<font color="red">Invalid Transaction Points</font><br/>

<?
foreach ($transArray as $invalidTran) {
    $teamName = $mapping[$invalidTran->team];
    $ptsGiven = $invalidTran->pts;
    $seasonUp = $invalidTran->year;
    print "<p>The $teamName don't have $ptsGiven reamining transactions in ";
    print "$seasonUp.  Please edit the trade to fix this problem.</p>";
}

?>
<input type="submit" value="Edit Trade">
<?
}
?>

</form>

<? include "base/footer.php"; ?>
</BODY>
</HTML>
