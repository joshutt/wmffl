<?
function trade($teamid, $date) {
	$tradequery="select t1.tradegroup, t1.date, tm1.name as TeamFrom, ";
	$tradequery.="p.lastname, p.firstname, p.position, p.nflteam, t1.other ";
	$tradequery.="from trade t1, trade t2, team tm1, team tm2 ";
    $tradequery.="left join players p on p.playerid=t1.playerid ";
	$tradequery.="where t1.tradegroup=t2.tradegroup and t1.teamfromid<>t2.teamfromid ";
	$tradequery.="and (t1.TeamFromid=$teamid or t1.TeamToid=$teamid) and t1.teamfromid=tm1.teamid ";
	$tradequery.="and t2.teamfromid=tm2.teamid ";
	$tradequery.="and t1.date='$date' ";
	$tradequery.="group by t1.tradegroup, abs(tm1.teamid-$teamid), p.lastname ";

	$results = mysql_query($tradequery);
	$oldgroup = 0;
	while (list($group, $date, $TeamFrom, $lastname, $firstname, $position, $nflteam, $other) = mysql_fetch_row($results)) {
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
require "base/conn.php";
	
	$thequery = "SELECT DATE_FORMAT(max(date), '%m/%e/%Y'), DATE_FORMAT(max(date),'%m'), DATE_FORMAT(max(date),'%Y') FROM transactions";
	$results = mysql_query($thequery);
	list($lastupdate, $themonth, $theyear) = mysql_fetch_row($results);
	
	if (isset($HTTP_GET_VARS["month"])) $themonth = $HTTP_GET_VARS["month"];
	if (isset($HTTP_GET_VARS["year"])) $theyear = $HTTP_GET_VARS["year"];
//	if (!isset($HTTP_GET_VARS["year"])) $HTTP_GET_VARS["year"]=2002;

    $title = "WMFFL Transactions";
include "base/menu.php";
?>

<H1 ALIGN=Center>Transactions</H1>
<H5 ALIGN=Center>Last Updated <?print $lastupdate;?></H5>
<HR size = "1">
<!--
<FORM ACTION="transactions.php" METHOD="GET">
<SELECT NAME="month">
	<OPTION VALUE="01"<? if ($themonth=='01') print "SELECTED";?>>January</OPTION>
	<OPTION VALUE="02"<? if ($themonth=='02') print "SELECTED";?>>February</OPTION>
	<OPTION VALUE="03"<? if ($themonth=='03') print "SELECTED";?>>March</OPTION>
	<OPTION VALUE="04"<? if ($themonth=='04') print "SELECTED";?>>April</OPTION>
	<OPTION VALUE="05"<? if ($themonth=='05') print "SELECTED";?>>May</OPTION>
	<OPTION VALUE="06"<? if ($themonth=='06') print "SELECTED";?>>June</OPTION>
	<OPTION VALUE="07"<? if ($themonth=='07') print "SELECTED";?>>July</OPTION>
	<OPTION VALUE="08"<? if ($themonth=='08') print "SELECTED";?>>August</OPTION>
	<OPTION VALUE="09"<? if ($themonth=='09') print "SELECTED";?>>September</OPTION>
	<OPTION VALUE="10"<? if ($themonth=='10') print "SELECTED";?>>October</OPTION>
	<OPTION VALUE="11"<? if ($themonth=='11') print "SELECTED";?>>November</OPTION>
	<OPTION VALUE="12"<? if ($themonth=='12') print "SELECTED";?>>December</OPTION>
</SELECT>
<INPUT TYPE="hidden" NAME="year" VALUE="2001">
-->
<!--
<SELECT NAME="year">
	<OPTION VALUE="2001">This Season</OPTION>
	<OPTION VALUE="2000">2000 Season</OPTION>
</SELECT>
-->
<!--
<INPUT TYPE="submit" NAME="submit" VALUE="Search">
</FORM>
-->
<?
include "history/2001Season/transmenu.html";

//	if (!isset($HTTP_POST_VARS["month"])) $HTTP_POST_VARS["month"]=$themonth;
//	if (!isset($HTTP_POST_VARS["year"])) $HTTP_POST_VARS["year"]=2001;

	// Create the query
	$thequery="SELECT DATE_FORMAT(t.date, '%M %e, %Y'), m.name, t.method, concat(p.firstname, ' ', p.lastname), p.position, p.nflteam, m.teamid, t.date ";
	$thequery .= "FROM transactions t, team m, players p ";
	$thequery .= "WHERE t.teamid=m.teamid AND t.playerid=p.playerid ";
	//$thequery .= "AND t.date BETWEEN '".$HTTP_GET_VARS["year"]."-".$themonth."-01' AND ";
	$thequery .= "AND t.date BETWEEN '".$theyear."-".$themonth."-01' AND ";
	$thequery .= "'".$theyear."-".$themonth."-31' ";
//	$thequery .= "'".HTTP_POST_VARS["year"]."-".$HTTP_POST_VARS["month"]."-31' ";
	$thequery .= "ORDER BY t.date DESC, m.name, t.method, p.lastname";
	
	
	$results = mysql_query($thequery);
	$first = TRUE;
	while (list($date, $teamname, $method, $player, $position, $nflteam, $teamid, $rawdate) = mysql_fetch_row($results)) {
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
					trade($teamid, $rawdate); 
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

include "base/footer.html";
?>

