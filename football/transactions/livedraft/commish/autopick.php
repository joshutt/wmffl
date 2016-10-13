<?php
require_once "utils/start.php";

if (isset($_REQUEST['teamid'])) {
    $autoteam = $_REQUEST['teamid'];
} else {
    $autoteam = 0;
}


$bigWhere = <<<EOD
p.pos NOT IN (
                SELECT p.pos FROM roster r
                JOIN newplayers p ON r.playerid = p.playerid
                WHERE r.dateoff IS NULL AND r.teamid = $autoteam
                GROUP BY p.pos
                HAVING count(r.playerid) >= 2
        )
EOD;


$query = <<<EOD

SELECT p.playerid, p.firstname, p.lastname, p.pos, sum(ps.pts), r.teamid
FROM newplayers p
JOIN playerscores ps ON p.playerid = ps.playerid
LEFT JOIN roster r ON p.playerid = r.playerid AND r.dateoff IS NULL
WHERE ps.season = 2015 AND ps.week <= 14 AND r.teamid IS NULL AND p.pos <> 'HC' and p.usePos=1 and p.pos<>'' 
AND $bigWhere 
GROUP BY p.playerid
ORDER BY sum(ps.pts) DESC;

EOD;

$results = mysql_query($query) or die("Unable to do query: ".mysql_error());
$row = mysql_fetch_array($results);

if ($row == NULL) {
	$query = <<<EOD

	SELECT p.playerid, p.firstname, p.lastname, p.pos, sum(ps.pts), r.teamid
	FROM newplayers p
	JOIN playerscores ps ON p.playerid = ps.playerid
	LEFT JOIN roster r ON p.playerid = r.playerid AND r.dateoff IS NULL
	WHERE ps.season = 2015 AND ps.week <= 14 AND r.teamid IS NULL AND p.pos <> 'HC'
	GROUP BY p.playerid
	ORDER BY sum(ps.pts) DESC;

EOD;

	$results = mysql_query($query) or die("Unable to do query: ".mysql_error());
	$row = mysql_fetch_array($results);
}

$autoDraft = True;
$autoPlayer = "id-" . $row["playerid"];

include "../setPick.php";

?>
