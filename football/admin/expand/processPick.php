<?
require_once "utils/start.php";

$teamid = $_REQUEST['team'];
$playerid = $_REQUEST['player'];


// Get the old team info
$sql = <<<EOD

select r.teamid, t.name
from roster r
join team t on r.teamid=t.teamid
where r.playerid=$playerid and (r.dateoff is null or r.dateoff > '2010-01-01');

EOD;

$results = mysql_query($sql) or die("Unable to get old team: ".mysql_error());
list($oldTeam, $teamName) = mysql_fetch_array($results);


// Get the pick info
$sql = <<<EOD
select if(max(round) is not null, max(round)+1, 1)
from expansionpicks;
EOD;

$results = mysql_query($sql) or die("Unable to determine round: ".mysql_error());
list($round) = mysql_fetch_array($results);


// Get the pullbacks
$sql = <<<EOD
select count(playerid)
from expansionprotections
where teamid=$oldTeam
and protected=0
and playerid <> $playerid
and type='pullback';
EOD;

$results = mysql_query($sql) or die("Unable to get number of pullbacks: ".mysql_error());
list($numPull) = mysql_fetch_array($results);

$sql = <<<EOD
select ep.playerid, concat(p.firstname, ' ', p.lastname) as 'name', p.pos, nr.nflteamid
from expansionprotections ep
JOIN newplayers p on ep.playerid=p.playerid
LEFT JOIN nflrosters nr on p.playerid=nr.playerid and nr.dateoff is null
where ep.teamid=$oldTeam
and ep.protected=0
and ep.playerid <> $playerid
and ep.type in ('pullback', if($numPull < 2, 'alternate', ''));
EOD;

$results = mysql_query($sql) or die("Unable to get pullback names: ".mysql_error());
$playerList = array();
while ($player = mysql_fetch_array($results)) {
    array_push($playerList, $player);
}

//print_r($playerList);

if (sizeof($playerList) < 2) {
    print "$teamName protect full roster";
} else {
    print "$teamName protects ";
    foreach($playerList as $player) {
        print "${player['name']} (${player['pos']}-${player['nflteamid']}), ";
    }
}

print "<br/>";

// Update Expansion Lost
$sql = <<<EOD
UPDATE expansionLost SET num=num+1 where teamid=$oldTeam;
EOD;

//print "$sql <br/>";
mysql_query($sql) or die("Unable to update expansionLost: ".mysql_error());


// Remove from old roster
$sql = <<<EOD
UPDATE roster SET dateoff=now()
WHERE playerid=$playerid and teamid=$oldTeam and dateoff is null;
EOD;

//print "$sql <br/>";
mysql_query($sql) or die("Unable to update old roster: ".mysql_error());


// Add to new roster
$sql = <<<EOD
INSERT INTO roster
(teamid, playerid, dateon)
VALUES
($teamid, $playerid, now());
EOD;

//print "$sql <br/>";
mysql_query($sql) or die("Unable to update new roster: ".mysql_error());


// Update Expansion picks
$sql = <<<EOD
INSERT INTO expansionpicks
(playerid, teamid, round)
VALUES
($playerid, $teamid, $round);
EOD;

//print "$sql <br/>";
mysql_query($sql) or die("Unable to update expansionPicks: ".mysql_error());


// Update expansion protections
//print_r($playerList);
if (sizeof($playerList) > 1) {
    $buildString = $playerList[0]['playerid'] . "," . $playerList[1]['playerid'];
} else {
    $buildString = $playerList[0]['playerid'];
}
$sql = <<<EOD
UPDATE expansionprotections
SET protected = 1
WHERE playerid in ($buildString);
EOD;

//print "$sql <br/>";
mysql_query($sql) or die("Unable to update expansionProtections: ".mysql_error());
print "Completed Successfully";
?>
