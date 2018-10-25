<?php
require_once "utils/connect.php";
require_once "utils/reportUtils.php";

if ($currentWeek < 1) {
    $thisSeason = $currentSeason - 1;
} else {
    $thisSeason = $currentSeason;
}

if (isset($_REQUEST["season"])) {
    $thisSeason = $_REQUEST["season"];
}

// Determine the format
$format = "html";
if (isset($_REQUEST["format"])) {
    $format = strtolower($_REQUEST["format"]);
}


$sql = "SELECT  t.name, p.pos, sum(ps.active) as 'totpts'
FROM playerscores ps
    JOIN newplayers p ON ps.playerid=p.playerid
    JOIN roster r ON r.PlayerID=p.playerid
    JOIN teamnames t ON r.teamid=t.teamid and ps.season=t.season
    JOIN weekmap w ON r.dateon <= w.activationdue and (r.dateoff is null or r.dateoff > w.activationdue)
WHERE w.season=$thisSeason and ps.season=w.season and ps.week=w.week
and ps.week<=14
and ps.active is not null
GROUP BY t.name, p.pos
ORDER BY t.name, p.pos";

$dateQuery = "SELECT max(week) FROM playerscores where season=$thisSeason and week<=14";

$results = mysqli_query($conn, $sql) or die("$sql<br/>" . mysqli_error());
$dateRes = mysqli_query($conn, $dateQuery);

list($week) = mysqli_fetch_row($dateRes);

$teamResults = array();
while ($teams = mysqli_fetch_array($results)) {
    if (!key_exists($teams["name"], $teamResults)) {
        $teamResults[$teams["name"]] = array("name" => $teams["name"]);
    }
    $teamResults[$teams["name"]][$teams["pos"]] = $teams["totpts"];
}

// For each team calculate the offense and defense and overall
foreach ($teamResults as $teamName) {
    $off = $teamName["HC"] + $teamName["QB"] + $teamName["RB"] + $teamName["WR"] + $teamName["TE"] + $teamName["K"] + $teamName["OL"];
    $def = $teamName["DL"] + $teamName["LB"] + $teamName["DB"];
    $teamName["offense"] = $off;
    $teamName["defense"] = $def;
    $teamName["total"] = $off + $def;
    $teamResults[$teamName["name"]] = $teamName;
}

$colTitles = array("Team", "HC", "QB", "RB", "WR", "TE", "K", "OL", "DL", "LB", "DB", "Offense", "Defense", "Total<br/>Pts");


if ($format == "html" || !supportedFormat($format)) {

    $javascriptList = array("//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js", "/base/js/jquery.tablesorter.min.js", "leaders.js");
    $cssList = array("stats.css");
    $title = "League Leaders";
    include "base/menu.php";
    ?>

    <H1 ALIGN=Center>League Leaders</H1>
    <HR>

    <? include "base/statbar.html"; ?>

    <div class="container mt-2">
        <span class="row justify-content-center my-2">Through Week <?= $week ?></span>
        <div class="formatOptions row col justify-content-center">
            <button class="button mx-1" id="csv" onClick="csv()">CSV</button>
            <button class="button mx-1" id="json" onClick="csv('json')">JSON</button>
        </div>
        <div id="mainTable" class="row col-12 justify-content-center">
            <?php outputHtml($colTitles, $teamResults); ?>
        </div>
    </div>
    <?php include "base/footer.html"; ?>

    <?php
} else if ($format == "csv") {
    outputCSV($colTitles, $teamResults, "leaders.csv");
} else if ($format == "json") {
    outputJSON($colTitles, $teamResults);
}
