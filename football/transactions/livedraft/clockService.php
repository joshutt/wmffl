<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/utils/start.php";
require_once "clock.class.php";

$sql = "SELECT * FROM config WHERE `key` like 'draft.clock.%'";
$results = mysql_query($sql) or die("Unable to get Config values: ".mysql_error());

$configArray = array();
while ($rows = mysql_fetch_assoc($results)) {
    $mainVal = $rows["key"];
    $broken = split("\.", $mainVal);
    $configArray[$broken[2]] = $rows["value"];
}

$sql = "SELECT t.name, t.teamid from draftpicks d join teamnames t on d.teamid=t.teamid and d.season=t.season where d.season=$currentSeason and d.playerid is null order by d.round, d.pick limit 1";
$results = mysql_query($sql) or die("Unable to get current pick: ".mysql_error());
$row = mysql_fetch_assoc($results);
$currentPick = $row["name"];
$teamId = $row["teamid"];

$sql = "SELECT t.name, c.value FROM `config` c JOIN teamnames t on substring_index(`key`, '.', -1) = t.teamid WHERE `key` like 'draft.team.%' and t.season=$currentSeason ORDER BY t.name ";
$results = mysql_query($sql) or die("Unable to get current pick: ".mysql_error());
$teamArray = array();
while ($rows = mysql_fetch_assoc($results)) {
    $teamArray[$rows["name"]] = $rows["value"];
}

$currentClockTime = getTotalTimeUsed($currentSeason);
#$availClockTime = $teamArray[$currentPick];
$availClockTime = getTimeAvail($teamId);
$remainTime = $availClockTime - $currentClockTime;

error_log("---------------------------------------------------------------------\n", 3, "check.log");
error_log("Current Clock $currentClockTime\n", 3, "check.log");
error_log("Available Clock $availClockTime\n", 3, "check.log");
error_log("Remain Time $remainTime\n", 3, "check.log");
error_log("All teams ".print_r($teamArray, TRUE)."\n", 3, "check.log");
error_log("---------------------------------------------------------------------\n", 3, "check.log");

if ($remainTime < 0) {
    $remainTime = 0;
}
//$remainTime = 144;

$expected = array("teamClocks" => $teamArray, "onClock" => $currentPick, "currentClock" => $remainTime, "config" => $configArray);

header("Content-Type: application/json");

print json_encode($expected);

