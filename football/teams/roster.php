<?
require_once "base/useful.php";
	$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y'), tp.TransPts+tp.ProtectionPts, tp.TotalPts, count(r.dateon)-count(r.dateoff)-1 from roster r, team t, transpoints tp where r.teamid=t.teamid and t.teamid=tp.teamid and t.teamid=$viewteam and tp.season=$currentSeason group by t.teamid";
$result = mysqli_query($conn, $thequery);
$theDate = mysqli_fetch_row($result);
?>


<TR><TD ALIGN=Center COLSPAN=2>
	<A NAME="Roster"><H3>Current Roster</H3></A>
	<H5><I>As of <?print $theDate[0];?></I><P>
	<?
	 $ptsLeft = $theDate[2] - $theDate[1];
	 if ($ptsLeft > 0) {
		print "$ptsLeft Remaining Free Transactions";
	 } else {
	      print abs($ptsLeft)." extra transactions used";
	 }
	print "<BR>$theDate[3] players on roster";
	?>
    </H5>
</TD></TR>

<TR><TD WIDTH=50% VALIGN=Top>
	<TABLE ALIGN=Left>
	<tr><th>Pos</th><th>Name</th><th>Bye</th><th>Team</th></tr>
<?
	//$teamname = $HTTP_POST_VARS["teamname"];
//	print "Team name: ".$teamname;
	//$thequery = "select p.lastname, p.pos, p.team, IF(p.firstname <> '', concat(', ',p.firstname), '') from newplayers p, roster r, team t where p.playerid=r.playerid and r.teamid=t.teamid and r.dateoff is null and t.teamid=$viewteam order by p.pos, p.lastname";
	 $thequery = "select p.lastname, p.pos, p.team, b.week,
		 IF(p.firstname <> '', concat(', ',p.firstname), '') 
		 from newplayers p
		 join roster r on p.playerid=r.playerid and r.dateoff is null
		 join team t on r.teamid=t.teamid 
		 left join nflbyes b on p.team=b.nflteam and b.season=$currentSeason
		 where t.teamid=$viewteam
		 order by p.pos, p.lastname";
$result = mysqli_query($conn, $thequery);
	$counter = 0;
$resultsize = mysqli_affected_rows($conn);
//	print "result size: ".$resultsize;
	while ($counter < $resultsize) {
        $player = mysqli_fetch_row($result);
		$counter++;
		$resultsize--;
		print "<TR><TD>".$player[1]."</TD><TD>".$player[0].$player[4]."</TD><TD>".$player[3]."</td><TD>".$player[2]."</TD></TR>";
	}	
?>
	</TABLE>
</TD><TD WIDTH=50% VALIGN=Top>
	<TABLE>
	<tr><th>Pos</th><th>Name</th><th>Bye</th><th>Team</th></tr>
	
<?
while ($player = mysqli_fetch_row($result)) {
		print "<TR><TD>".$player[1]."</TD><TD>".$player[0].$player[4]."</TD><TD>".$player[3]."</td><TD>".$player[2]."</TD></TR>";
	}
mysqli_close($conn);
?>

	</TABLE>
</TD></TR>
	

