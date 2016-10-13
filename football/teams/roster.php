<?
require_once "$DOCUMENT_ROOT/base/useful.php";
	//$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y'), tp.PtsLeft, tp.PrePtsLeft, count(r.dateon)-count(r.dateoff)-1 from roster r, team t, transpoints tp where r.teamid=t.teamid and t.teamid=tp.teamid and t.name='".$teamname."' group by t.teamid";
	$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y'), tp.TransPts+tp.ProtectionPts, tp.TotalPts, count(r.dateon)-count(r.dateoff)-1 from roster r, team t, transpoints tp where r.teamid=t.teamid and t.teamid=tp.teamid and t.teamid=$viewteam and tp.season=$currentSeason group by t.teamid";
	//$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y'), tp.TransPts+tp.ProtectionPts, tp.TotalPts, count(r.dateon)-count(r.dateoff)-1 from roster r, team t, transpoints tp where r.teamid=t.teamid and t.teamid=tp.teamid and t.name='".$teamname."' and tp.season=$currentSeason group by t.teamid";
	//$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y'), tp.TransPts+tp.ProtectionPts, tp.TotalPts, count(r.dateon)-count(r.dateoff)-1 from roster r, team t, newtranspoints tp where r.teamid=t.teamid and t.teamid=tp.teamid and t.name='".$teamname."' and tp.season=2003 group by t.teamid";
	$result = mysql_query($thequery, $conn);	
	$theDate = mysql_fetch_row($result);
//	print $theDate[0];
?>


<TR><TD ALIGN=Center COLSPAN=2>
	<A NAME="Roster"><H3>Current Roster</H3></A>
	<H5><I>As of <?print $theDate[0];?></I><P>
    <!--
	<? if ($theDate[2] > 0) print "$theDate[2] remaining pre-season transactions<BR>";
	  if ($theDate[1] > 0) {
	  	print "$theDate[1] Remaining Free Transactions";
	  } else {
	  	print abs($theDate[1])." Extra Transactions Used";
	  }
	?>
    -->
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
<!--<TR><TD>&nbsp;</TD></TR>-->

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
	$result = mysql_query($thequery, $conn);
	$counter = 0;
	$resultsize = mysql_affected_rows();
//	print "result size: ".$resultsize;
	while ($counter < $resultsize) {
		$player = mysql_fetch_row($result);
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
	while ($player = mysql_fetch_row($result)) {
		print "<TR><TD>".$player[1]."</TD><TD>".$player[0].$player[4]."</TD><TD>".$player[3]."</td><TD>".$player[2]."</TD></TR>";
	}	
	mysql_close($conn);
?>

	</TABLE>
</TD></TR>
	

