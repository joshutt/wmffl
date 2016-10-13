
<TR><TD WIDTH=50% VALIGN=Top>
	<TABLE ALIGN=Left>
<?
require_once "$DOCUMENT_ROOT/base/conn.php";
	//$teamname = $HTTP_POST_VARS["teamname"];
//	print "Team name: ".$teamname;
	$thequery = "select p.fullname, p.position, p.nflteam from players p, roster r, team t where p.playerid=r.playerid and r.teamid=t.teamid and r.dateoff is null and t.name='".$teamname."' order by p.position, p.name";
	$result = mysql_query($thequery, $conn);
	$counter = 0;
	$resultsize = mysql_affected_rows();
//	print "result size: ".$resultsize;
	while ($counter < $resultsize) {
		$player = mysql_fetch_row($result);
		$counter++;
		$resultsize--;
		print "<TR><TD>".$player[1]."</TD><TD>".$player[0]."</TD><TD>".$player[2]."</TD></TR>";
	}	
?>
	</TABLE>
</TD><TD WIDTH=50% VALIGN=Top>
	<TABLE>
	
<?
	while ($player = mysql_fetch_row($result)) {
		print "<TR><TD>".$player[1]."</TD><TD>".$player[0]."</TD><TD>".$player[2]."</TD></TR>";
	}	
	mysql_close($conn);
?>

	</TABLE>
</TD></TR>
	

