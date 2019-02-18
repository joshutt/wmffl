<?
require_once "utils/start.php";

if (!isset($_POST["submit"])) {
		header("Location: protections");
		exit;
	}
	
	$playerlist = "(";
	if (!empty($protect)) {
		foreach ($protect as $value) {
			$playerlist .= "$value, ";
		}
	}
	$playerlist .= "0) ";
	
	
	$checkQuery = "select max(pos.cost) as 'cost' ";
	$checkQuery .= "from newplayers p ";
    $checkQuery .= "join roster r on p.playerid=r.playerid and r.dateoff is null ";
    $checkQuery .= "join positioncost pos on pos.position=p.pos ";
	$checkQuery .= "left join protectioncost pc on ";
	$checkQuery .= "p.playerid=pc.playerid ";
    $checkQuery .= "and pc.season=$currentSeason ";
	$checkQuery .= "left join protections pro on ";
	$checkQuery .= "pro.playerid=p.playerid and ";
	$checkQuery .= "pro.teamid=r.teamid and pro.season=$currentSeason ";
	$checkQuery .= "where r.teamid=$teamnum ";
	$checkQuery .= "and (pos.years<=pc.years or pos.years=0) ";
	$checkQuery .= "and p.playerid in $playerlist ";
	$checkQuery .= "GROUP BY p.playerid";
	
	$checkCost = "SELECT protectionpts, totalpts FROM transpoints ";
	$checkCost .= "WHERE teamid=$teamnum and season=$currentSeason";
	
	$delQuery = "DELETE FROM protections WHERE season=$currentSeason ";
	$delQuery .= "AND teamid=$teamnum";
	
	$insQuery = "INSERT INTO protections (teamid, playerid, season, cost) ";
	$insQuery .= "select r.teamid, p.playerid, $currentSeason, ";
	$insQuery .= "max(pos.cost) as 'cost' ";
	$insQuery .= "from newplayers p ";
    $insQuery .= "join roster r on p.playerid=r.playerid and r.dateoff is null ";
    $insQuery .= "join positioncost pos on pos.position=p.pos ";
	$insQuery .= "left join protectioncost pc ";
	$insQuery .= "on p.playerid=pc.playerid ";
    $insQuery .= "and pc.season=$currentSeason ";
	$insQuery .= "where r.teamid=$teamnum and ";
	$insQuery .= "(pos.years<=pc.years or pos.years=0) ";
	$insQuery .= "and p.playerid in $playerlist ";
	$insQuery .= "GROUP BY p.playerid";
	
	$detCost = "SELECT sum(cost) FROM protections ";
	$detCost .= "WHERE teamid=$teamnum AND Season=$currentSeason";
	
	$updCost = "UPDATE transpoints SET protectionpts=";
	
	$display = "select CONCAT(p.firstname, ' ', p.lastname), ";
	$display .= "p.pos, p.team, pro.cost ";
	$display .= "from newplayers p, protections pro ";
	$display .= "where p.playerid=pro.playerid ";
	$display .= "and pro.season=$currentSeason and pro.teamid=$teamnum ";
	$display .= "order by p.pos, p.lastname ";

$title = "WMFFL Protections";    

	include "base/menu.php";
?>

<H1 ALIGN=Center>Protections</H1>
<HR size = "1">
	
<?	
if ($isin) {
	
	// Gather costs
//	print "<P>$checkQuery</P>";
	$result = mysql_query($checkQuery);
	$totalCost = 0;
	while (list($thiscost) = mysql_fetch_row($result)) {
		$totalCost += $thiscost;
	}
	//print "<P>$checkCost</P>";
	$result = mysql_query($checkCost);
	$thiscost = mysql_fetch_row($result);
	if ($totalCost > $thiscost[1]) {
		print "<P><B>You spent too many protection points<BR>";
		print "Spent: $totalCost<BR>";
		print "Allowed: $thiscost[0]</B></P>";
	} else {
	
		// Remove old and insert new protections
//	print "<P>$delQuery</P>";
		mysql_query($delQuery) or die("Delete Query");
//	print "<P>$insQuery</P>";
		mysql_query($insQuery) or die("Insert Query: ".mysql_error());
		
		// Determine the new cost
//	print "<P>$detCost</P>";
//		$result = mysql_query($detCost) or die("Cost Query");
//		$cost = mysql_fetch_row($result);
//		$updCost .= $cost[0];
		$updCost .= $totalCost;
		$updCost .= " WHERE teamid=$teamnum and season=$currentSeason";
//		print "<P>$updCost</P>";
		mysql_query($updCost) or die("Update Cost Query");
		
		print "<P><B>Your protections have been saved.  ";
		print "You may revise them anytime until the deadline.</B></P>";

		print "<TABLE>";
		print "<TR><TH>Player</TH><TH></TH><TH>Cost</TH></TR>";
		$results = mysql_query($display);
		while (list($player, $pos, $team, $cost) = mysql_fetch_row($results)) {
			print "<TR><TD>$player ($pos-$team)</TD><TD>&nbsp;</TD><TD>$cost</TD></TR>";
		}
		print "<TR><TH>TOTAL</TH><TH></TH><TH>$totalCost</TH></TR>";
		print "</TABLE>";
	}
	print "<P><A HREF=\"protections">Change Protections</A></P>";
} else {
	print "<P><B>You must be logged in to save protections</B></P>";
}
?>
<?
	include "base/footer.html";
?>

