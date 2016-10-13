<?
	$conn = mysql_connect('localhost', 'wmffl_gquery', 'gamble');
	mysql_select_db("wmffl_gamble");	

	$x = 1;
    $alreadyquery = "SELECT p.teampicked FROM picks p, users u ";
    $alreadyquery .= "WHERE p.teamid=u.userid AND u.userid=$x";

	$allnflquery = "SELECT * FROM nflteams order by city";

	$results = mysql_query($alreadyquery);

	$allpicked = array();
	while ($pickedid = mysql_fetch_array($results)) {
		$allpicked.push($pickedid[0]);
	}

	$results = mysql_query($allnflquery);
	
	while ($nflteam = mysql_fetch_array($results)) {
		if (in_array($nflteam["nflid"], $allpicked)) {
			print $nflteam["city"]." ".$nflteam["mascot"];
		} else {
			print "<B>".$nflteam["city"]." ".$nflteam["mascot"]."</B>";
		}
		print "<BR>";
	}
?>
