<?php
require_once "utils/start.php";
require_once "utils/injuryUtils.php";

if (!$isin) {
    header('HTTP/1.0 401 Unauthorized');
    print "User is not logged in";
    exit();
}

// Get the parameters
$method = $_REQUEST["method"] ?? Null;
$player = $_REQUEST["playerid"] ?? Null;

// Make sure method is valid
if ($method !== "Add" && $method !== "Remove") {
    exit();
}

// Make sure player is int
$playerid = (int)$player;

// Make sure player is on team's roster and elgible
$return = "";
if ($method === "Add") {
    // If Add, then create IR record
    $insertQuery = "insert into ir
(playerid, current, dateon)
select p.playerid, 1, now()
from newplayers p
join roster r on p.playerid=r.PlayerID and r.DateOff is null
join weekmap wm on now() between wm.StartDate and wm.EndDate
left join ir on p.playerid=ir.playerid and ir.dateoff is null
left join newinjuries inj on p.playerid=inj.playerid and wm.Season=inj.season and wm.week=inj.week
where p.playerid=? and r.teamid=? and ir.id is null and inj.status in " . getIRStatusSql();

    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $playerid, $teamnum);
    $stmt->execute();

    $rows = $stmt->affected_rows;
    if ($rows) {
        $return = "Player $playerid Added to IR";
    } else {
        $return = "Unable to add $playerid to IR";
    }

} else if ($method === "Remove") {
    // If Remove, then close IR record
    $updateQuery = "update newplayers p
join roster r on p.playerid=r.PlayerID and r.DateOff is null
join weekmap wm on now() between wm.StartDate and wm.EndDate
left join ir on p.playerid=ir.playerid and ir.dateoff is null
set ir.dateoff=now()
where p.playerid=? and r.teamid=? and ir.id is not null";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $playerid, $teamnum);
    $stmt->execute();

    $rows = $stmt->affected_rows;
    if ($rows) {
        $return = "Player $playerid removed from IR";
    } else {
        $return = "Unable to remove $playerid from IR";
    }



}


header('Content-type: application/json');
print json_encode($return);
