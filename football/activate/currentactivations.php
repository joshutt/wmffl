<?
require_once "base/conn.php";
require_once "utils/start.php";

// Set Week variable
if (isset($_REQUEST["week"])) {
    $week = $_REQUEST["week"];
} else if (!isset($week)) {
    $week = $currentWeek;
}

// Set Season variable
if (isset($_REQUEST["season"])) {
    $season = $_REQUEST["season"];
} else if (!isset($season)) {
    $season = $currentSeason;
}

$select = <<<EOD
select tn.name, p.pos, p.lastname, p.firstname, r.nflteamid, g.kickoff, 
UNIX_TIMESTAMP()-UNIX_TIMESTAMP(g.kickoff) as 'remain', s.gameid, g.homeTeam, g.roadTeam, 
i.status, i.details, CONVERT_TZ(wm.ActivationDue, 'SYSTEM', 'GMT') as 'ActivationDue'
from teamnames tn
join schedule s on tn.teamid in (s.teama, s.teamb) and tn.season=s.season
left join revisedactivations a on a.season=s.season and a.week=s.week and a.teamid in (s.TeamA, s.TeamB) and tn.teamid=a.teamid
left join newplayers p on a.playerid=p.playerid 
join weekmap wm on s.season=wm.season and s.week=wm.week 
left join injuries i on i.playerid=p.playerid and i.season=wm.season and i.week=wm.week 
left join nflrosters r on r.dateon<= wm.activationDue and (r.dateoff >= wm.activationDue or r.dateoff is null) and r.playerid=p.playerid
left join nflgames g on a.season=g.season and a.week=g.week and r.nflteamid in (g.homeTeam, g.roadTeam)
where s.season=$season and s.week=$week
order by s.gameid, a.teamid, p.pos, p.lastname, p.playerid
EOD;

$gamePlanSelect = <<<EOD
SELECT t.teamid, t.name, t.season, g1.week, p1.firstname as 'forName', p1.lastname as 'forLast', p1.pos as 'forPos',
p1.team as 'forTeam', p2.firstname as 'agstName', p2.lastname as 'agstLast', p2.pos as 'agstPos', p2.team as 'agstTeam'
FROM teamnames t
LEFT JOIN gameplan g1 on t.teamid=g1.teamid and t.season=g1.season and g1.week=$week and g1.side='Me'
LEFT JOIN newplayers p1 on g1.playerid=p1.playerid
LEFT JOIN gameplan g2 ON t.teamid=g2.teamid and t.season=g2.season and g2.week=$week and g2.side='Them'
LEFT JOIN newplayers p2 on g2.playerid=p2.playerid
WHERE t.season=$season
EOD;


#print $select;

$result = mysqli_query($conn, $select) or die("Select: " . mysqli_error($conn));

// Popuolate records
$lastTeam = "";
$printer = array();
$gpLine = array();
$nameIdMap = array();
$i = 0;
//print "<br/>";
$actDue = null;
$lastName = "";
while ($row = mysqli_fetch_assoc($result)) {
    if ($row["remain"] < -30*60) {
        $lock = false;
    } else {
        $lock = true;
    }

    // Get the players name
    if ($row["name"] != $lastName) {
        $i++;
        $lastName = $row["name"];
        $nameIdMap[$lastName] = $i;
		$printer[$i] = "<table>";
		$printer[$i] .= "<TR><TH colspan=\"4\">".$row["name"]."</TH></TR>";
    }

    // Determine is player is locked
    if ($lock) {
        $spot = "*";
    } else {
        $spot = "";
    }

    // Set Act Due
    if (!isset($actDue)) {
        $actDue = $row["ActivationDue"];
    }

    // Determine opponent
    $opp = "Bye";
    if ($row["nflteamid"] == $row["homeTeam"]) {
        $opp = "vs ".$row["roadTeam"]; 
    } else if ($row["nflteamid"] == $row["roadTeam"]) {
        $opp = "@ ".$row["homeTeam"]; 
    }

    // Determine injusy status
    $injury_detail = "";
    switch ($row["status"]) {
        case 'P': $injury_detail = 'Probable: '; break;
        case 'Q': $injury_detail = 'Questionable: '; break;
        case 'D': $injury_detail = 'Doubtful: '; break;
        case 'O': $injury_detail = 'Out: '; break;
        case 'S': $injury_detail = 'Suspension: '; break;
    }
    $injury_detail .= $row["details"];

    // Build the line to print
    $printer[$i] .= "<tr><td>".$spot.$row["pos"]."</td>";
    $printer[$i] .= "<td>".$row["firstname"]." ".$row["lastname"]."</td>";
    if ($row["status"] != "") {
        $printer[$i] .= "<td><span class=\"PQDO\" title=\"$injury_detail\">(".$row["status"].")</span></td>";
    } else {
        $printer[$i] .= "<td></td>";
    }
    $printer[$i] .= "<td>".$row["nflteamid"]."</td><td>$opp</td>";
    $printer[$i] .= "</tr>";
    
}

?>


<table>
<tr><td colspan=3 align=center><b>Current Activations for Week <?= $week ?></b></td></tr>

<?php
for ($i = 1;
$i <= sizeof($printer) / 2;
$i++) {
    ?>
    <tr>
        <td valign=top><?= $printer[2 * $i - 1] ?></table></td>
    <td width="5%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td valign=top><?= $printer[2 * $i] ?></table></td></tr>
    <tr><td>&nbsp;</td></tr>
<?php } ?>

</table>
