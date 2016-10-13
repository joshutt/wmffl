<?
	require_once "$DOCUMENT_ROOT/base/conn.php";
	$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y') from roster r, team t where r.teamid=t.teamid and t.name='".$teamname."'";
	$result = mysql_query($thequery, $conn);	
	$theDate = mysql_fetch_row($result);
	print $theDate;
?>


<TR><TD WIDTH=50% VALIGN=Top>
	<TABLE ALIGN=Left>
<?
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
	

