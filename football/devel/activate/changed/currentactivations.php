<?
require_once "$DOCUMENT_ROOT/base/conn.php";
require_once "$DOCUMENT_ROOT/utils/start.php";

if (isset($_REQUEST["week"])) {
    $week = $_REQUEST["week"];
} else if (isset($week)) {
    $week = $week;
} else {
    $week = $currentWeek;
}

if (isset($_REQUEST["season"])) {
    $season = $_REQUEST["season"];
} else if (isset($season)) {
    $season = $season;
} else {
    $season = $currentSeason;
}

$select = <<<EOD
select tn.name, p.pos, p.lastname, p.firstname, r.nflteamid, g.kickoff, UNIX_TIMESTAMP()-UNIX_TIMESTAMP(g.kickoff) as 'remain', s.gameid
from revisedactivations a
join newplayers p on a.playerid=p.playerid
join teamnames tn on tn.season=a.season and tn.teamid=a.teamid
join weekmap wm on a.season=wm.season and a.week=wm.week
join nflrosters r on r.dateon<= wm.activationDue and (r.dateoff >= wm.activationDue or r.dateoff is null) and r.playerid=p.playerid
join schedule s on a.season=s.season and a.week=s.week and a.teamid in (s.TeamA, s.TeamB)
left join nflgames g on a.season=g.season and a.week=g.week and r.nflteamid in (g.homeTeam, g.roadTeam)
where a.season=$season and a.week=$week
order by s.gameid, a.teamid, p.pos, p.lastname, p.playerid
EOD;

//print $select;

$result = mysql_query($select, $conn) or die("Select: ".mysql_error());

// Popuolate records
$lastTeam = "";
$printer = array();
$i = 0;
//print "<br/>";
while ($row = mysql_fetch_assoc($result)) {
    if ($row["remain"] < -30*60) {
        $lock = false;
    } else {
        $lock = true;
    }
    //print "{$row["name"]} - {$row["pos"]} - {$row["firstname"]} {$row["lastname"]} - {$row["nflteamid"]} - {$row["kickoff"]} - {$row["remain"]} - $lock<br/>";

    if ($row["name"] != $lastName) {
        $i++;
        $lastName = $row["name"];
		$printer[$i] = "<TABLE>";
		$printer[$i] .= "<TR><TH COLSPAN=3>".$row["name"]."</TH></TR>";
    }
    if ($lock) {
        $spot = "*";
    } else {
        $spot = "";
    }

    $printer[$i] .= "<tr><td>".$spot.$row["pos"]."</td>";
    $printer[$i] .= "<td>".$row["firstname"]." ".$row["lastname"]."</td>";
    $printer[$i] .= "<td>".$row["nflteamid"]."</td></tr>";
    
}


// display teams in schedule order
print "<TABLE>";
print "<TR><TD COLSPAN=3 ALIGN=center><B>Current Activations for Week $week</B></TD></TR>";

foreach (range(1,5) as $i) {
    print "<TR><TD VALIGN=top>".$printer[2*$i - 1]."</table></TD>";
    print "<TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
    print "<TD VALIGN=top>".$printer[2*$i]."</table></TD></TR>";
    print "<TR><TD>&nbsp;</TD></TR>";
}

print "</TABLE>";
?>
