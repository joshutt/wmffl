<?
require_once "$DOCUMENT_ROOT/base/conn.php";
	
	// define statements
	srand ((float) microtime() * 1000000);
	$tablename = "tmpact".rand();
	$create = "CREATE TABLE $tablename (teamid INT(11) DEFAULT '0' NOT NULL, Season YEAR(4) DEFAULT '0000' NOT NULL, Week   TINYINT(4)  DEFAULT '0' NOT NULL)";
	$select = "select t.name, p.position, IF (p.firstname <> '', concat(p.lastname, ', ', p.firstname), p.lastname), p.nflteam, p.statid, t.statid, w.week from team t, players p, activations a, $tablename w where a.teamid=t.teamid and a.teamid=w.teamid and a.week=w.week and a.season=w.season and p.playerid in (a.HC,a.QB,a.RB1,a.RB2,a.WR1,a.WR2,a.TE,a.K,a.OL, a.DL1,a.DL2,a.LB1,a.LB2,a.DB1,a.DB2) group by t.teamid, p.position, p.lastname, p.firstname, p.playerid";
	$drop = "DROP TABLE IF EXISTS $tablename";
	
	if (isset($HTTP_GET_VARS["week"])) {
		$week = $HTTP_GET_VARS["week"];
	} else if (isset($HTTP_POST_VARS["week"])) {
		$week = $HTTP_POST_VARS["week"];
	} else {
		$pickweek = "SELECT week FROM weekmap WHERE EndDate>=now() and StartDate<=now()";
		$result = mysql_query($pickweek, $conn) or die("Week Pick");
		$row = mysql_fetch_row($result);
		$week = $row[0];
	}
	
	
	$insert = "INSERT INTO $tablename SELECT a.teamid, w.season, MAX(a.week) FROM activations a, weekmap w WHERE a.season=w.season and a.week<=$week GROUP BY a.teamid";
	

	// Perform queries
	mysql_query($create, $conn) or die("Create");
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
		$printer[$i] = "Week:$week \nSeason:2001\nTeamID:".$activates[$i][0][5]."\n";
		for ($j=0; $j<$activates[$i]["count"]; $j++) {
			$printer[$i] .= "PlayerID:".$activates[$i][$j][4]."\n";  
		}
		mail ("activations@wmffl.com", "$i Activations for Week $week", $printer[$i], "From: webmaster@$SERVER_NAME");
	}
?>


