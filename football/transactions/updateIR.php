<?php
require_once "utils/start.php";
require_once "IRResource.php";
require_once "utils/injuryUtils.php";

if (!$isin) {
    header('HTTP/1.0 401 Unauthorized');
    print "User is not logged in";
    exit();
}

// Get the parameters
$method = $_REQUEST["method"] ?? Null;
$playerid = $_REQUEST["playerid"] ?? Null;

// Make sure method is valid
if ($method !== "Add" && $method !== "Remove") {
    exit();
}

// Make sure player is int
$resource = new IRResource($conn, $teamnum);
$player = new IRPlayer((int)$playerid);

// Make sure player is on team's roster and elgible
$return = "";
if ($method === "Add") {
    // If Add, then create IR record
    $success = $resource->addPlayerToIR($player);
    if ($success) {
        $return = "Player {$player->playerid} Added to IR";
    } else {
        $return = "Unable to add {$player->playerid} to IR";
    }

} else if ($method === "Remove") {
    // If Remove, then close IR record
    $success = $resource->removePlayerFromIR($player);
    if ($success) {
        $return = "Player $playerid removed from IR";
    } else {
        $return = "Unable to remove $playerid from IR";
    }
}


header('Content-type: application/json');
print json_encode($return);
