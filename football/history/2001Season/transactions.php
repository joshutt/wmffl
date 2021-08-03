<?php
function trade($conn, $teamid, $date)
{
    $tradequery = "select t2.tradegroup, t1.date, tm1.name as TeamFrom,
p.lastname, p.firstname, p.pos, r.nflteamid, t1.other
from trade t1
 join trade t2 on t1.TradeGroup=t2.TradeGroup
 join team tm1 on t1.TeamFromID=tm1.TeamID
 JOIN team tm2 on t2.TeamFromID=tm2.TeamID and tm1.TeamID != tm2.TeamID
left join newplayers p on p.playerid=t1.playerid
left join nflrosters r on p.playerid=r.playerid and r.dateon <= t1.Date and (r.dateoff >= t1.Date or r.dateoff is null)
where (t1.TeamFromid=$teamid or t1.TeamToid=$teamid)
and t1.date='$date'
group by t1.tradegroup, abs(tm1.teamid-$teamid), p.lastname";

    $results = mysqli_query($conn, $tradequery) or die("Error: " . mysqli_error($conn));
	$oldgroup = 0;
    $firstteam = "";
    $firstplayer = TRUE;
    /** @noinspection PhpUnusedLocalVariableInspection */
    while (list($group, $tdate, $TeamFrom, $lastname, $firstname, $position, $nflteam, $other) = mysqli_fetch_row($results)) {
		if ($oldgroup != $group) {
			print "<LI>Traded ";
			$oldgroup = $group;
			$firstteam = $TeamFrom;
			$firstplayer = TRUE;
		}
		if ($firstteam != $TeamFrom) {
			print " to the $TeamFrom in exchange for ";
			$firstplayer = TRUE;
			$firstteam = $TeamFrom;
		}
		if (!$firstplayer) {print ", ";}
        if ($other) {print $other;}
		else print "$firstname $lastname ($position-$nflteam)";
		$firstplayer = FALSE;
	}
}


	// Include the file that defines the connection information
require "utils/connect.php";
	
	$thequery = "SELECT DATE_FORMAT(max(date), '%m/%e/%Y'), DATE_FORMAT(max(date),'%m'), DATE_FORMAT(max(date),'%Y') FROM transactions";
$results = mysqli_query($conn, $thequery);
list($lastupdate, $themonth, $theyear) = mysqli_fetch_row($results);

if (isset($_REQUEST["month"])) $themonth = $_REQUEST["month"];
if (isset($_REQUEST["year"])) $theyear = $_REQUEST["year"];
//	if (!isset($_GET["year"])) $_GET["year"]=2002;

    $title = "WMFFL Transactions";
include "base/menu.php";
?>

<H1 ALIGN=Center>Transactions</H1>
<H5 ALIGN=Center>Last Updated <?print $lastupdate;?></H5>
<HR size = "1">
<?php
include "history/2001Season/transmenu.html";

//	if (!isset($_POST["month"])) $_POST["month"]=$themonth;
//	if (!isset($_POST["year"])) $_POST["year"]=2001;

	// Create the query
$thequery = "SELECT DATE_FORMAT(t.date, '%M %e, %Y'), m.name, t.method, concat(p.firstname, ' ', p.lastname), p.pos, r.nflteamid, m.teamid, t.date
FROM transactions t
 JOIN teamnames m on t.TeamID=m.teamid and m.season=$theyear
  JOIN newplayers p on t.PlayerID=p.playerid
left JOIN nflrosters r on t.PlayerID=r.playerid and r.dateon <= t.Date and (r.dateoff>=t.Date or r.dateoff is null)
WHERE t.date BETWEEN '$theyear-$themonth-01' AND
'$theyear-$themonth-31'
ORDER BY t.date DESC, m.name, t.method, p.lastname";


$results = mysqli_query($conn, $thequery);
	$first = TRUE;
$olddate = "";
$oldteam = "";
$oldmethod = "";
while (list($date, $teamname, $method, $player, $position, $nflteam, $teamid, $rawdate) = mysqli_fetch_row($results)) {
		$change = FALSE;
		if ($olddate != $date) {
			if (!$first) {
				print "</UL></UL>";
			}
			$first = FALSE;
			print "<B><I>$date</I></B><UL>";
			$olddate = $date;
			$change = TRUE;
			$firstplayer = TRUE;
			$tradeonce = FALSE;
		}
		if ($oldteam != $teamname || $change) {
			if (!$change) print "</UL>";
			print "<LI><B>$teamname</B><UL>";
			$oldteam = $teamname;
			$change = TRUE;
			$firstplayer = TRUE;
			$tradeonce = FALSE;
		}
		if ($oldmethod != $method || $change) {
			switch($method) {
				case 'Cut':  print "<LI>Dropped "; break;
				case 'Sign': print "<LI>Picked Up "; break;
				case 'Trade':
					if ($tradeonce) continue 2;
                    trade($conn, $teamid, $rawdate);
					$change = TRUE;
					$oldmethod = "";
					$tradeonce = TRUE;
					continue 2;
				case 'Fire': print "<LI>Fired "; break;
				case 'Hire': print "<LI>Hired "; break;
			}
//			print "<LI>$method ";
			$oldmethod = $method;
			$change = TRUE;
			$firstplayer = TRUE;
		}
		if (!$firstplayer) print ", ";
		print "$player ($position-$nflteam)";
		$firstplayer = FALSE;
	}
	print "</UL></UL>";

include "base/footer.php";

