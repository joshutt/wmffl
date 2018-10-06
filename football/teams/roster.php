<?php
include "utils/reportUtils.php";

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

<TR>
    <TD VALIGN=Top>
<?
	//$teamname = $HTTP_POST_VARS["teamname"];
//	print "Team name: ".$teamname;
	//$thequery = "select p.lastname, p.pos, p.team, IF(p.firstname <> '', concat(', ',p.firstname), '') from newplayers p, roster r, team t where p.playerid=r.playerid and r.teamid=t.teamid and r.dateoff is null and t.teamid=$viewteam order by p.pos, p.lastname";
	 $thequery = "select p.lastname, p.pos, p.team, b.week,
		 IF(p.firstname <> '', concat(', ',p.firstname), '') as 'firstname'
		 from newplayers p
		 join roster r on p.playerid=r.playerid and r.dateoff is null
		 join team t on r.teamid=t.teamid 
		 left join nflbyes b on p.team=b.nflteam and b.season=$currentSeason
		 where t.teamid=$viewteam
		 order by p.pos, p.lastname";
$result = mysqli_query($conn, $thequery);
$hold = array();
while ($player = mysqli_fetch_array($result)) {
    $newItem = array("pos" => $player["pos"], "name" => $player["lastname"] . $player["firstname"], "team" => $player["team"], "bye" => $player["week"]);
    array_push($hold, $newItem);
}
mysqli_close($conn);

$titles = array("Pos", "Name", "Team", "Bye");
outputHtml($titles, $hold);
?>

</TD></TR>
	

