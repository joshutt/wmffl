<?
require_once "base/conn.php";
require_once "utils/start.php";
require_once "utils/injuryUtils.php";

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
i.status, i.details, CONVERT_TZ(wm.ActivationDue, 'SYSTEM', 'GMT') as 'ActivationDue', ir.current as 'ir'
from teamnames tn
join schedule s on tn.teamid in (s.teama, s.teamb) and tn.season=s.season
left join revisedactivations a on a.season=s.season and a.week=s.week and a.teamid in (s.TeamA, s.TeamB) and tn.teamid=a.teamid
left join newplayers p on a.playerid=p.playerid 
join weekmap wm on s.season=wm.season and s.week=wm.week 
left join newinjuries i on i.playerid=p.playerid and i.season=wm.season and i.week=wm.week 
left join ir on ir.playerid=p.playerid and ir.dateoff is null
left join nflrosters r on r.dateon<= wm.activationDue and (r.dateoff >= wm.activationDue or r.dateoff is null) and r.playerid=p.playerid
left join nflgames g on a.season=g.season and a.week=g.week and r.nflteamid in (g.homeTeam, g.roadTeam)
where s.season=$season and s.week=$week
order by s.gameid, a.teamid, p.pos, p.lastname, p.playerid
EOD;

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
    if ($row["remain"] < -30 * 60) {
        $lock = false;
    } else {
        $lock = true;
    }

    // Get the players name
    if ($row["name"] != $lastName) {
        if ($lastName !== "") {
            $printer[$i] .= "</div>";
            $printer[$i] .= "</div>";
//            $printer[$i] .= "</div>";  // Closes previous card
        }
        $i++;
        $lastName = $row["name"];
        $nameIdMap[$lastName] = $i;
        $printer[$i] = "<div class='card my-4 mx-0' >";
        $printer[$i] .= "<div class='card-header font-weight-bold'>" . $row["name"] . "</div>";
        $printer[$i] .= "<div class='card-body'>";
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
        $opp = $row["nflteamid"] . " vs " . $row["roadTeam"];
    } else if ($row["nflteamid"] == $row["roadTeam"]) {
        $opp = $row["nflteamid"] . " @ " . $row["homeTeam"];
    }


    // Determine injury status
    $pqdoLine = getPQDOLine($row["status"], $row["details"], $row["ir"]);

    // Build the line to prin
    $printer[$i] .= "<div class='row my-1'><div class='col-2'>" . $spot . $row["pos"] . "</div>";
    $printer[$i] .= "<div class='col-4 text-left'>" . $row["firstname"] . " " . $row["lastname"] . "</div>";
    $printer[$i] .= "<div class='col-3 mr-0'>" . $opp . "</div>";
    $printer[$i] .= "<div class='col-2 ml-0 pl-0'>$pqdoLine</div>";
    $printer[$i] .= "</div>"; // should close row
    $printer[$i] .= "<!-- close row -->";

}
$printer[$i] .= "</div></div>";  // close the last card

?>

<!--<tr><td colspan=3 align=center><b>Current Activations for Week --><? //= $week ?><!--</b></td></tr>-->

<?php
for ($i = 1;
     $i <= sizeof($printer) / 2;
     $i++) {
    ?>
    <div class="row" id="row<?=$i?>">
        <div class="col ml-2 mr-0" id="col<?=$i?>odd"><?= $printer[2 * $i - 1] ?></div>
        <div class="col mr-2 ml-0" id="col<?=$i?>even"><?= $printer[2 * $i] ?></div>
    </div>

<?php } ?>

