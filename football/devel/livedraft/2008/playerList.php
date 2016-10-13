<?
require_once "$DOCUMENT_ROOT/utils/start.php";
$pos = $_REQUEST["pos"];
$nfl = $_REQUEST["nfl"];

$sql = "SELECT p.playerid, p.lastname, p.firstname, p.pos, r.nflteamid FROM newplayers p, nflrosters r left join roster wr on wr.playerid=p.playerid and wr.dateoff is null WHERE p.playerid=r.playerid and r.dateoff is null and usePos=1 and active=1 and wr.teamid is null ";
if ($pos != "*") {
    $sql .= "and p.pos='$pos' ";
}
if ($nfl != "*") {
    $sql .= "and r.nflteamid='$nfl' ";
}
$sql .= "order by p.lastname, p.firstname ";

$result = mysql_query($sql) or die("Ug I died: ".mysql_error());

while($playerList = mysql_fetch_array($result)) {
    //print "{$playerList["playerid"]} - {$playerList["lastname"]}, {$playerList["firstname"]} - {$playerList["pos"]} - {$playerList["team"]}";
    print "<option value=\"{$playerList["playerid"]}\">{$playerList["lastname"]}, {$playerList["firstname"]} - {$playerList["pos"]} - {$playerList["nflteamid"]}</option>";
}


?>
