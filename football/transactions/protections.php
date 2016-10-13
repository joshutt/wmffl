<?
require_once "utils/start.php";
#$isin = true;
#$teamnum = 2;
$dateSrc = "2016-08-22 04:00 EDT";
$dateTime = new DateTime($dateSrc);

$thequery = "select p.playerid, p.firstname, p.lastname, p.pos, ";
$thequery .= "p.team, ";
$thequery .= "if (pc.years is null, 0, pc.years) as 'Years', ";
$thequery .= "max(pos.cost) as 'Cost', ";
$thequery .= "if (pro.cost is null, 0, 1) as 'Protected' ";
$thequery .= "from newplayers p ";
$thequery .= "join roster r on p.playerid=r.playerid and r.dateoff is null ";
$thequery .= "join positioncost pos on p.pos=pos.position and pos.startSeason<=$currentSeason and pos.endSeason is null ";
$thequery .= "left join protectioncost pc ";
$thequery .= "on p.playerid=pc.playerid ";
$thequery .= "and pc.season=$currentSeason ";
$thequery .= "left join protections pro ";
//$thequery .= "on pro.playerid=p.playerid and pro.teamid=r.teamid ";
$thequery .= "on pro.playerid=p.playerid ";
$thequery .= "and pro.season=$currentSeason ";
//$thequery .= "and pro.season=pc.season ";
$thequery .= "where r.teamid=$teamnum ";
$thequery .= "and (pos.years<=pc.years or pos.years=0) ";
$thequery .= "GROUP BY p.playerid ";
$thequery .= "ORDER BY p.pos, p.lastname, p.firstname";

//$ptsQuery = "select PrePtsLeft, PtsLeft from transpoints where teamid=$teamnum";
$ptsQuery = "select TotalPts, ProtectionPts from transpoints where teamid=$teamnum and season=$currentSeason";
//$ptsQuery = "select TotalPts, ProtectionPts from newtranspoints where teamid=$teamnum and season=2003";

$title = "WMFFL Protections";
?>

<?
	include "base/menu.php";
?>


<H1 ALIGN=Center>Protections</H1>
<HR size = "1">

<?
if ($isin) {
	$results = mysql_query($ptsQuery) or die("Database error: ".mysql_error());
	$pts = mysql_fetch_row($results);
?>

<SCRIPT LANGUAGE="JavaScript">

function change(numPts, index) {
	if (document["protform"]["protect[]"][index].checked) {
		newVal = eval(document["protform"].PtsUse.value) + eval(numPts);
	} else {
		newVal = eval(document["protform"].PtsUse.value) - eval(numPts);
	}
	document["protform"].PtsUse.value = newVal;
}

function checkForm() {
	if (eval(document["protform"].PtsUse.value) <= eval(<? print $pts[0]; ?>)) 
		return true;
	alert ("You protections exceed <? print $pts[0]; ?> points");
	return false;
}

</SCRIPT>

<P>
Select the players that you wish to protect, by checking the box next to
them and then submitting the form.  You may change protections at any time
up until the deadline: <?= $dateTime->format("h:i a T \o\\n l, F d");?>.</P>

<?
	if ($dateTime->getTimestamp() <= time()) {
?>
<P><B><FONT COLOR="red">Sorry, The Deadline For Changing Protections
has Passed</FONT></B></P>
<?
	} else {
?>

<FORM NAME="protform" ACTION="saveprotections.php" METHOD="POST">

<?
	print "Points Allowed: <B>$pts[0]</B><BR>";
	print "Points Used: <INPUT TYPE=\"TEXT\" NAME=\"PtsUse\" SIZE=\"3\" MAXLENGTH=\"3\" onFocus=\"this.blur();\" VALUE=\"$pts[1]\">";
?>


<TABLE>
<TR><TH></TH><TH>Name</TH><TH>Position</TH><TH>Cost</TH></TR>
<? 
	// Create the query
	$results = mysql_query($thequery);
	$idx = 0;
	while (list($playerid, $firstname, $lastname, $pos, $nfl, $year, $cost, $protected) = mysql_fetch_row($results)) {
		print "<TR><TD><INPUT TYPE=\"checkbox\" NAME=\"protect[]\" VALUE=\"$playerid\" ONCLICK=\"change($cost, $idx)\"";
		if ($protected == 1 || $pos=="HC") print "CHECKED ";
		print "></TD>";
		print "<TD>$firstname $lastname ($pos-$nfl)</TD><TD>$pos</TD><TD>$cost</TR>";
		$idx++;
	}
?>	
</TABLE>

<INPUT TYPE=SUBMIT NAME="submit" VALUE="Submit Protections" onClick="return checkForm()">
</FORM>
	
<?
}
	} else {
?>

<CENTER><B>You must be logged in to submit protections</B></CENTER>

<? } ?>
	
<?
	include "base/footer.html";
?>

