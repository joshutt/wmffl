<?
require_once "utils/start.php";
include "utils/reportUtils.php";


// If a player has fewer weeks than max fill with nulls
function checkMaxGames($player, $max)
{
    if (sizeof($player) < $max + 5) {
        $len = sizeof($player) - 5;
        for ($i = $len + 1; $i <= $max; $i++) {
            $player[$i] = null;
        }
    }
    return $player;
}

// Move the tot column to the end of the player array
function moveTot($player)
{
    $name = array_shift($player);
    $pos = array_shift($player);
    $nfl = array_shift($player);
    $team = array_shift($player);
    $tot = array_shift($player);
    array_unshift($player, $name, $pos, $nfl, $team);
    array_push($player, $tot);
    return $player;
}

function checkForAllNull($player)
{
    if ($player["tot"] != 0) {
        return true;
    }
    for ($i = 5; $i < sizeof($player); $i++) {
        if (!array_key_exists($i, $player)) {
            continue;
        }
        if ($player[$i] != null) {
            return true;
        }
    }
    return false;
}


function cmp($a, $b)
{
    if ($a["tot"] == $b["tot"]) {
        return ($a["name"] > $b["name"]);
    }
    return ($a["tot"] > $b["tot"]) ? -1 : 1;
}


// Search by Team
$byPos = false;
if (isset($_REQUEST["team"]) && $_REQUEST["team"] != "") {
    $searchTeam = $_REQUEST["team"];
} else if (isset($_REQUEST["pos"]) && $_REQUEST["pos"] != "") {
    $searchPos = mysqli_real_escape_string($conn, $_REQUEST["pos"]);
    $byPos = true;
} else if (array_key_exists('teamnum', $_SESSION) && $_SESSION["teamnum"] != "") {
    $searchTeam = $_SESSION["teamnum"];
} else {
    $searchTeam = 1;
}


// Determine the season to use
if (isset($_REQUEST["season"])) {
    $season = $_REQUEST["season"];
} else if ($currentWeek == 0) {
    $season = $currentSeason - 1;
} else {
    $season = $currentSeason;
}


// Search by Position

if (isset($searchTeam)) {

    $sql = <<<EOD
select p.playerid, p.firstname, p.lastname, p.pos, ps.season as 'season', ps.week as 'week', ps.pts as 'pts', t.abbrev, p.team as 'nfl'
from newplayers p
left join roster r on p.playerid=r.playerid and r.dateoff is null
left join teamnames t on r.teamid=t.teamid and t.season=$season
left join playerscores ps on p.playerid=ps.playerid and ps.season=$season
where r.teamid=$searchTeam 
order by p.pos, p.lastname, p.firstname, ps.week
EOD;

} else if ($byPos) {

    $sql = <<<EOD
select p.playerid, p.firstname, p.lastname, p.pos, ps.season as 'season', ps.week as 'week', ps.pts as 'pts', t.abbrev, p.team as 'nfl'
from newplayers p
left join roster r on p.playerid=r.playerid and r.dateoff is null
left join teamnames t on r.teamid=t.teamid and t.season=$season
left join playerscores ps on p.playerid=ps.playerid and ps.season=$season
where p.pos='$searchPos' and p.active=1
order by p.pos, p.lastname, p.firstname, ps.week
EOD;

}

$results = mysqli_query($conn, $sql) or die("There was an error in the query: " . mysqli_error($conn));
$newHold = array();
$max = 0;
while ($playList = mysqli_fetch_array($results)) {
    // If the new player isn't already in array add it
    $id = $playList["playerid"];
    if (sizeof($newHold) == 0 || !array_key_exists($id, $newHold)) {
        $newHold[$id] = array("name" => $playList["firstname"] . " " . $playList["lastname"], "pos" => $playList["pos"], "nfl" => $playList["nfl"], "team" => $playList["abbrev"], "tot" => 0);
        $lastNum = 0;
    }

    // Fill in blank weeks
    $week = $playList["week"];
    if ($week == null) {
        $week = 1;
    }
    if ($week > $lastNum + 1) {
        for ($i = $lastNum + 1; $i < $week; $i++) {
            $newHold[$id][$i] = null;
        }
    }

    // Add the week
    $newHold[$id][$week] = $playList["pts"];
    $newHold[$id]["tot"] += $playList["pts"];
    $lastNum = $week;
    if ($week > $max) {
        $max = $week;
    }
}

if ($byPos) {
    $newHold = array_filter($newHold, "checkForAllNull");
    usort($newHold, "cmp");
}
$newHold = array_map("checkMaxGames", $newHold, array_fill(1, sizeof($newHold), $max));
$newHold = array_map("moveTot", $newHold);

// Build titles
$titles = array("Name", "Pos", "NFL", "Team");
//$titles = array_merge($titles, range(1, 16));
$titles = array_merge($titles, range(1, $max));
array_push($titles, "Tot");

// Determine the format
$format = "html";
if (isset($_REQUEST["format"])) {
    $format = strtolower($_REQUEST["format"]);
}

// Display output
if ($format == "html" || !supportedFormat($format)) {
    $title = "Week By Week";
    $javascriptList = array("//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js", "/base/vendor/js/jquery.tablesorter.min.js", "week.js");
    $cssList = array("week.css", "stats.css");
    include_once "base/menu.php";
    ?>
    <h1 align="center"><?= $title ?></h1>
    <hr size="1">


    <?
    include "base/statbar.html";
    print "<div id=\"tblblock\" class='container justify-content-center'>";
    include "weekbyweekinc.php";
    print "<div id=\"mainTable\" class='row col-12 justify-content-center'>";
    outputHtml($titles, $newHold);
    print "</div></div>";
    include "base/footer.php";
} else if ($format == "ajax") {
    outputHtml($titles, $newHold);
} else if ($format == "csv") {
    outputCSV($titles, $newHold, "weekbyweek.csv");
} else if ($format == "json") {
    outputJSON($titles, $newHold);
}
?>
