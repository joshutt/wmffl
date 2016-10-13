<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$teamId = $_REQUEST["teamid"];

$sql = <<<EOD

SELECT p.pos, CONCAT(p.firstName, ' ', p.lastName) as 'playername', t.name, t.teamid, r.dateon, n.nflteamid as 'nfl'
FROM newplayers p
JOIN roster r ON r.playerid=p.playerid and r.dateoff is null
JOIN teamnames t ON r.teamid=t.teamid
LEFT JOIN nflrosters n on p.playerid=n.playerid and n.dateoff is null
WHERE t.season=$currentSeason and t.teamid=$teamId
ORDER BY t.name, p.pos, p.lastname

EOD;

$results = mysql_query($sql) or die("Error: ".mysql_error());
$body = "";
$team = "";
$first = true;
$count = 0;
while($row = mysql_fetch_assoc($results)) {
    if ($first) {
        $body .= "<tr><th colspan=\"3\">{$row["name"]}</th></tr>";
        $first = false;
    }
    
    if ($count % 2) {
        $class = "oddtablerow";
    } else {
        $class = "eventablerow";
    }
    
    $name = trim($row["playername"]);
    $body .= "<tr class=\"$class\"><td class=\"pos\">{$row["pos"]}</td><td class=\"player\">$name</td><td class=\"team\">{$row["nfl"]}</td></tr>";
    $count++;
}
$body .= "</team>";

header("Content-type: text/html");

$xmlOutput = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$xmlOutput .= "<roster timestamp=\"$maxPick\">\n";
$xmlOutput .= $body;
$xmlOutput .= "</roster>\n";

$xmlOutput = "<table width=\"100%\" align=\"center\" valign=\"top\" class=\"rosterReport\">";
$xmlOutput .= $body;
$xmlOutput .= "</table>";
print $xmlOutput;

?>
