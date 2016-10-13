<?
require_once "$DOCUMENT_ROOT/utils/start.php";

require "DataObjects/Draftpicks.php";
require "DataObjects/Config.php";

$draftPicks = new DataObjects_Draftpicks;
$draftPicks->Season = $currentSeason;
$draftPicks->orderBy("Round");
$draftPicks->orderBy("Pick");
$draftPicks->find();

$config = new DataObjects_Config;

$body = "";
$nextPick = "";
$maxPick = 0;
while ($draftPicks->fetch()) {
    $roundDist = sprintf("%02d", $draftPicks->Round);
    $pickDist = sprintf("%02d", $draftPicks->Pick);
    $team = $draftPicks->getLink("teamid");
    #$teamName = sprintf("%20.20s", $team->Name);
    $teamName = $team->Name;
    $teamId = $team->TeamID;
    $player = $draftPicks->getLink("playerid");
    $timeStamp = $draftPicks->pickTime;
    $timeStamp = strtotime($timeStamp);
    if ($timeStamp > $maxPick) {
        $maxPick = $timeStamp;
    }
    
    if ($player != null) {
        $playerName = $player->firstname.' '.$player->lastname.' ('.$player->pos.'-'.$player->team.')';
        $playerId = $player->flmid;
        $playerPos = $player->pos;
        $playerTeam = $player->team;
        $body .= "<draftPick round=\"$roundDist\" pick=\"$pickDist\" timestamp=\"$timeStamp\">\n";
        $body .= "<player id=\"$playerId\" pos=\"$playerPos\" team=\"$playerTeam\">$playerName</player>\n";
        $body .= "<franchise id=\"$teamId\">$teamName</franchise>\n";
        $body .= "</draftPick>\n";
    } else {
        $config->key = "draft.clock.start";
        $config->find(1);
        $startClock = $config->value;
        //$startClock = strtotime($startClock);
        if ($startClock > $maxPick) {
            $maxPick = $startClock;
        }

        if ($nextPick == "") {
            $diff = 120 - (time() - $maxPick);
            if ($diff < 0) {$diff = 0;}
            if ($diff > 120) {$diff = 120;}
            $nextPick = "<nextPick round=\"$roundDist\" pick=\"$pickDist\" time=\"$diff\" max=\"$maxPick\" start=\"$startClock\" rtime=\"".time()."\">";
            $nextPick .= "<franchise id=\"$teamId\">$teamName</franchise>";
            $nextPick .= "</nextPick>\n";
        } else if ($onDeckPick == "") {
            $onDeckPick = "<onDeck round=\"$roundDist\" pick=\"$pickDist\">";
            $onDeckPick .= "<franchise id=\"$teamId\">$teamName</franchise>";
            $onDeckPick .= "</onDeck>\n";
        }
        $body .= "<draftPick round=\"$roundDist\" pick=\"$pickDist\">\n";
        $body .= "<franchise id=\"$teamId\">$teamName</franchise>\n";
        $body .= "</draftPick>\n";
    }
}

$config2 = new DataObjects_Config;
$config2->key = "draft.clock.run";
$config2->find(1);
$pausedValue = $config2->value;
if ($pausedValue == "true") {
    $pausedValue = "false";
} else {
    $pausedValue = "true";
}

//exit();

header("Content-type: text/xml");

$xmlOutput = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
//$xmlOutput .= "<draftResults timestamp=\"".time()."\">\n";
$xmlOutput .= "<draftResults timestamp=\"$maxPick\" paused=\"$pausedValue\">\n";
$xmlOutput .= $nextPick;
$xmlOutput .= $onDeckPick;
$xmlOutput .= $body;
$xmlOutput .= "</draftResults>\n";

print $xmlOutput;
?>
