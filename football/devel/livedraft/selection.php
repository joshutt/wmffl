<?
require_once "$DOCUMENT_ROOT/utils/start.php";
$season = $currentSeason;

require "DataObjects/Draftpicks.php";
require "DataObjects/Team.php";
require "DataObjects/Roster.php";
require "DataObjects/Newplayers.php";

$team = $_REQUEST["team"];
$player = $_REQUEST["player"];


$errors = array();
if ($team == null || trim($team) == "") {
    array_push($errors, "No Selection Team Given");
}

if ($player == null || trim($player) == "") {
    array_push($errors, "No Player Selected");
}

    /*
    $fp = fopen('data.txt', 'a');
    $output = print_r($logArr, true);
    fwrite($fp,$output);
    fwrite($fp,"\nTeam is: $team  Player is: $player \n");
    fwrite($fp, "Set: ".isset($logArr)."\n");
    fwrite($fp, "Search: ".array_search($team, $logArr));
    fclose($fp);
    */

if ((!isset($logArr) || !array_search($team, $logArr)) && !$commish) {
    array_push($errors, "Must be logged in as a team to make a selection");
}


// Get the current Pick
// Make sure this is the correct team for it
$draftPicks = new DataObjects_Draftpicks;
$draftPicks->Season = $season;
$draftPicks->whereAdd("playerid is null");
$draftPicks->orderBy("Round");
$draftPicks->orderBy("Pick");
$draftPicks->find(true);

$teamPicked = new DataObjects_Team;
$teamPicked->TeamID = $team;
$teamPicked->find(true);

if ($team != $draftPicks->teamid) {
    $otherTeam = $draftPicks->getLink('teamid'); 
    array_push($errors, $otherTeam->Name." are on the clock, not ".$teamPicked->Name);
}

// Make sure that this player exists and is not already taken
$rosterPlayer = new DataObjects_Roster;
$rosterPlayer->PlayerID = $player;
$rosterPlayer->whereAdd("DateOff is null");
$rosterPlayer->find(true);

$playerInfo = new DataObjects_Newplayers;
$playerInfo->get($player);

if (!$playerInfo->N) {
    array_push($errors, "The playerId $player does not exisit");
}

if ($rosterPlayer->N) {
    array_push($errors, "{$playerInfo->firstname} {$playerInfo->lastname} is already on a team");
}


if (sizeof($errors) > 0) {
    $resp = "Illegal Draft Pick: \n";
    foreach ($errors as $err) {
        $resp .= "\t$err\n";
    }
    print $resp;
} else {

    // Save player as the selected one
    $sql = "UPDATE draftpicks SET playerid=$player where Season=$season and Round={$draftPicks->Round} and Pick={$draftPicks->Pick}";
    mysql_query($sql) or die ("MySQL Error #1: ".mysql_error());
    
    // Save the player onto the roster (NOT YET THOUGH)
    $sql = "INSERT INTO roster (playerid, teamid, dateon) VALUES ($player, $team, now())";
    mysql_query($sql) or die ("MySQL ERROR #2: ".mysql_error());
    
    print "{$teamPicked->Name} successfully picked {$playerInfo->firstname} {$playerInfo->lastname}";

    // Delete the roster Item that was just set
}
?>
