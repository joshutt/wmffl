<?
require_once "$DOCUMENT_ROOT/base/conn.php";
	
	// define statements
	srand ((float) microtime() * 1000000);
	$tablename = "tmpact".rand();
	$create = "CREATE TABLE $tablename (teamid INT(11) DEFAULT '0' NOT NULL, Season YEAR(4) DEFAULT '0000' NOT NULL, Week   TINYINT(4)  DEFAULT '0' NOT NULL)";
	$select = "select t.name, p.position, IF (p.firstname <> '', concat(p.lastname, ', ', p.firstname), p.lastname), p.nflteam from team t, players p, activations a, $tablename w where a.teamid=t.teamid and a.teamid=w.teamid and a.week=w.week and a.season=w.season and p.playerid in (a.HC,a.QB,a.RB1,a.RB2,a.WR1,a.WR2,a.TE,a.K,a.OL, a.DL1,a.DL2,a.LB1,a.LB2,a.DB1,a.DB2) group by t.teamid, p.position, p.lastname, p.firstname, p.playerid";
	$drop = "DROP TABLE IF EXISTS $tablename";
	if (!isset($Week) && isset($HTTP_POST_VARS["week"])) {
		$Week = $HTTP_POST_VARS["week"];
	}
	if (isset($Week)) {
		$insert = "INSERT INTO $tablename SELECT a.teamid, w.season, MAX(a.week) FROM activations a, weekmap w WHERE a.season=w.season and a.week<=".$Week." GROUP BY a.teamid";
		$groupteams = "select t1.name, t2.name, w.weekname, IF(w.ActivationDue>now(),concat('As of ',date_format(now(),'%l:%i %p on %W')),'Final and Offical') from weekmap w, schedule s, team t1, team t2 where s.Season=w.season and s.week=w.week and w.week=".$HTTP_POST_VARS["week"]." and s.teama=t1.teamid and s.teamb=t2.teamid";	
	} else {
		$insert = "INSERT INTO $tablename SELECT a.teamid, w.season, MAX(a.week) FROM activations a, weekmap w WHERE a.season=w.season and a.week<=w.week and w.EndDate>=now() and w.StartDate<=now() GROUP BY a.teamid";
		$groupteams = "select t1.name, t2.name, w.weekname, IF(w.ActivationDue>now(),concat('As of ',date_format(now(),'%l:%i %p on %W')),'Final and Offical') from weekmap w, schedule s, team t1, team t2 where s.Season=w.season and s.week=w.week and s.teama=t1.teamid and s.teamb=t2.teamid and now()>=w.startdate and now()<=w.enddate";
	}
	

	// Perform queries
	mysql_query($create, $conn) or die("Create $tablename");
	mysql_query($insert, $conn) or die("Insert");
	$result = mysql_query($select, $conn) or die("Select");
	
	// Populate records
	while ($row = mysql_fetch_row($result)) {
		if (!isset($activates[$row[0]]["count"])) $activates[$row[0]]["count"]=0;
		$activates[$row[0]][$activates[$row[0]]["count"]++] = $row;
	}
	mysql_query($drop, $conn) or die("Drop");
	
	
	// populate team output 
	while (list ($i, $val) = each ($activates)) {
		$printer[$i] = "<TABLE>";
		$printer[$i] .= "<TR><TH COLSPAN=3>".$activates[$i][0][0]."</TH></TR>";
		for ($j=0; $j<$activates[$i]["count"]; $j++) {
			$printer[$i] .= "<TR><TD>".$activates[$i][$j][1]."</TD>";
			$printer[$i] .= "<TD>".$activates[$i][$j][2]."</TD>";
			$printer[$i] .= "<TD>".$activates[$i][$j][3]."</TD></TR>";
		}
		$printer[$i] .= "</TABLE>";
	}
	
	// display teams in schedule order
	print "<TABLE>";
	$result = mysql_query($groupteams, $conn) or die("Teams");
	$firsttime=TRUE;
	while ($onegame = mysql_fetch_row($result)) {
		if ($firsttime) {
			print "<TR><TD COLSPAN=3 ALIGN=center><B>Current Activations for ".$onegame[2]."</B></TD></TR>";
			print "<TR><TD COLSPAN=3 ALIGN=center><B>".$onegame[3]."</B></TD></TR>";
			$firsttime = FALSE;
		}
		print "<TR><TD VALIGN=top>".$printer[$onegame[0]]."</TD>";
		print "<TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
		print "<TD VALIGN=top>".$printer[$onegame[1]]."</TD></TR>";
		print "<TR><TD>&nbsp;</TD></TR>";
	}
	print "</TABLE>";

?>

