<?
// establish connection
require "base/conn.php";

$updateQuery1 = <<<EOD
INSERT INTO transactions (teamid, playerid, method, Date)
SELECT r.teamid, p.playerid, 'Fire', now()
FROM roster r, newplayers p
WHERE r.playerid=p.playerid AND r.dateoff is null
and p.pos='HC' AND r.teamid=$team;
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
mysql_query($updateQuery1) or die("Dead: ".mysql_error());
mysql_query($updateQuery2) or die("Dead: ".mysql_error());
mysql_query($updateQuery3) or die("Dead: ".mysql_error());
mysql_query($updateQuery4) or die("Dead: ".mysql_error());

?>
<b>Head Coach Changed</b><br/>
<a href="index.html">Return to Admin page</a>
