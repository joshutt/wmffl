<?
require_once "utils/start.php";

function cmp($a, $b) {
    if ($a["pct"] > $b["pct"]) {
        return 1;
    } else if ($a["pct"] < $b["pct"]) {
        return -1;
    } else if ($a["games"] > $b["games"]) {
        return 1;
    } else if ($a["games"] < $b["games"]) {
        return -1;
    } else if ($a["wins"] > $b["wins"]) {
        return 1;
    } else if ($a["wins"] < $b["wins"]) {
        return -1;
    } else {
        return 0;
    }
}

function displayBlock($array, $wties = true) {
    $count = 0;
    foreach ($array as $team) {
        $disPCT = sprintf("%3.3f", $team["pct"]);
        if ($team["active"] == 0) {
            $dec = "<i>";
            $ced = "</i>";
        } else {
            $dec = "";
            $ced = "";
        }
        if ($count%2 == 0) {
            $bgcolor = "#cccccc";
        } else {
            $bgcolor = "#ffffff";
        }
        $count++;
        if ($wties) {
            print <<<EOD
    <TR><TD class="text-left">$dec{$team["name"]}$ced</TD>
    <TD>$dec{$team["games"]}$ced</TD><TD>$dec{$team["wins"]}$ced</TD>
    <TD>$dec{$team["losses"]}$ced</TD><TD>$dec{$team["ties"]}$ced</TD>
    <TD>$dec$disPCT$ced</TD></TR>
EOD;
        } else {
            print <<<EOD
    <TR><TD class="text-left">$dec{$team["name"]}$ced</TD>
    <TD>$dec{$team["games"]}$ced</TD><TD>$dec{$team["wins"]}$ced</TD>
    <TD>$dec{$team["losses"]}$ced</TD><TD>$dec$disPCT$ced</TD></TR>
EOD;
        }
    }
}

function getRecList($addWhere, $season) {
    global $conn;
    $alltimeQuery =<<<EOD
        select t.name, t.active, count(s.gameid) as 'games',
        sum(if(t.teamid=s.TeamA, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb>s.scorea, 1, 0))) as 'wins',
        sum(if(t.teamid=s.TeamA, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb<s.scorea, 1, 0))) as 'losses',
        sum(if(s.scorea=s.scoreb, 1, 0)) as 'ties'
        from team t, schedule s
        where t.teamid in (s.TeamA, s.TeamB) and s.season < $season

EOD;
    $groupBy = "group by t.teamid";

    $finalQuery = $alltimeQuery." ".$addWhere." ".$groupBy;
    $result = mysqli_query($conn, $finalQuery) or die("Dead alltime query: " . $finalQuery . "<br/>Error: " . mysqli_error($conn));

    $recordsArray = array();
    while ($team = mysqli_fetch_array($result)) {
        $pct = ($team["wins"] + $team["ties"]/2.0) / $team["games"];
        $team["pct"]=$pct;
        array_push($recordsArray, $team);
    }
    usort($recordsArray, "cmp");
    return array_reverse($recordsArray);
}


$allTimeArray = getRecList("", $currentSeason); 
$regSeasonArray = getRecList("and postseason=0", $currentSeason);
$postSeasonArray = getRecList("and postseason=1", $currentSeason);
$playoffArray = getRecList("and playoffs=1", $currentSeason);
$championshipArray = getRecList("and championship=1", $currentSeason);
$toiletBowlArray = getRecList("and postseason=1 and playoffs=0", $currentSeason);


$title = "WMFFL ALL-Time Records";
?>

<?
include "base/menu.php";
?>

<H1 ALIGN=CENTER>All-Time Win Loss Records</H1>
<H5 ALIGN=CENTER>Through <? print $currentSeason-1; ?> Season</H5>
<HR size = "1">

<div class="text-center font-weight-bold ">Overall Records</div>
<table class="table table-striped table-hover table-sm text-center table-sortable"> 
<thead>
<tr>
<th scope="col" class="text-left">Team</th>
<th scope="col">Games</th>
<th scope="col">Wins</th>
<th scope="col">Losses</th>
<th scope="col">Ties</th>
<th scope="col">PCT</th>
</tr>
</thead>
<tbody>
<? displayBlock($allTimeArray); ?>
</tbody>
</TABLE>

<div class="text-center font-weight-bold ">Regular Season Records</div>
<table class="table table-striped table-hover table-sm text-center table-sortable"> 
<thead>
<tr>
<th scope="col" class="text-left">Team</th>
<th scope="col">Games</th>
<th scope="col">Wins</th>
<th scope="col">Losses</th>
<th scope="col">Ties</th>
<th scope="col">PCT</th>
</tr>
</thead>
<tbody>
<? displayBlock($regSeasonArray); ?>
</TABLE>

<div class="text-center font-weight-bold ">Post-Season Records</div>
<table class="table table-striped table-hover table-sm text-center table-sortable"> 
<thead>
<tr>
<th scope="col" class="text-left">Team</th>
<th scope="col">Games</th>
<th scope="col">Wins</th>
<th scope="col">Losses</th>
<th scope="col">PCT</th>
</tr>
</thead>
<tbody>
<? displayBlock($postSeasonArray, false); ?>
</tbody>
</TABLE>

<div class="text-center font-weight-bold ">Playoff Records</div>
<table class="table table-striped table-hover table-sm text-center table-sortable"> 
<thead>
<tr>
<th scope="col" class="text-left">Team</th>
<th scope="col">Games</th>
<th scope="col">Wins</th>
<th scope="col">Losses</th>
<th scope="col">PCT</th>
</tr>
</thead>
<tbody>
<? displayBlock($playoffArray, false); ?>
</tbody>
</table>

<div class="text-center font-weight-bold ">Championship Game Records</div>
<table class="table table-striped table-hover table-sm text-center table-sortable"> 
<thead>
<tr>
<th scope="col" class="text-left">Team</th>
<th scope="col">Games</th>
<th scope="col">Wins</th>
<th scope="col">Losses</th>
<th scope="col">PCT</th>
</tr>
</thead>
<tbody>
<? displayBlock($championshipArray, false); ?>
</tbody>
</table>


<div class="text-center font-weight-bold ">Toilet Bowl Game Records</div>
<table class="table table-striped table-hover table-sm text-center table-sortable"> 
<thead>
<tr>
<th scope="col" class="text-left">Team</th>
<th scope="col">Games</th>
<th scope="col">Wins</th>
<th scope="col">Losses</th>
<th scope="col">PCT</th>
</tr>
</thead>
<tbody>
<? displayBlock($toiletBowlArray, false); ?>
</tbody>
</table>

<? include "base/footer.html" ?>
