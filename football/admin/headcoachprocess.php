<?
// establish connection
require "base/conn.php";

$team = $_REQUEST['team'];
$player = $_REQUEST['player'];

$updateQuery1 = <<<EOD
INSERT INTO transactions (teamid, playerid, method, Date)
SELECT r.teamid, p.playerid, 'Fire', now()
FROM roster r
JOIN newplayers p on r.playerid=p.playerid AND r.dateoff is null
WHERE p.pos='HC' AND r.teamid=$team;
EOD;

$updateQuery2 = <<<EOD
INSERT INTO transactions (teamid, playerid, method, Date)
VALUES ($team, $player, 'Hire', now());
EOD;

$updateQuery3 = <<<EOD
UPDATE roster r, newplayers p
SET r.dateoff=now()
WHERE r.playerid=p.playerid AND r.dateoff is null
AND p.pos='HC' AND r.teamid=$team;
EOD;

$updateQuery4 = <<<EOD
INSERT INTO roster (teamid, playerid, dateon)
VALUES ($team, $player, now());
EOD;


#print $updateQuery;
mysqli_query($conn, $updateQuery1) or die("Dead1: " . mysqli_error($conn) . " -- " . $updateQuery1);
mysqli_query($conn, $updateQuery2) or die("Dead2: " . mysqli_error($conn));
mysqli_query($conn, $updateQuery3) or die("Dead3: " . mysqli_error($conn));
mysqli_query($conn, $updateQuery4) or die("Dead4: " . mysqli_error($conn));

?>
<b>Head Coach Changed</b><br/>
<a href="index.html">Return to Admin page</a>
