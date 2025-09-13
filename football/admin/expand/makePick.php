<?php
include '../check.inc.php';
require_once "utils/start.php";

$sql = <<<EOD
SELECT ex.playerid, t.name as 'teamid', concat(ex.firstname, ' ', ex.lastname) as 'name', ex.pos, if (el.num>=2 or exp.teamid is not null, 1, ep.protected) as 'protected', ex.cost, r.nflteamid
from expandAvailable ex
join team t on ex.teamid=t.name
join expansionLost el on t.teamid=el.teamid
left join expansionprotections ep on ex.playerid=ep.playerid
left join nflrosters r on ex.playerid=r.playerid and r.dateoff is null
left join expansionpicks exp on exp.playerid=ex.playerid
    order by pos, lastname
EOD;
?>

<form action="processPick.php">
<select name="team"><option value="12">Matt</option><option value="13">Bill</option></select>

<select name="player">
<?
$results = mysqli_query($conn, $sql) or die("Unable to get available: " . mysqli_error($conn));

while ($player = mysqli_fetch_array($results)) {
    if ($player['protected'] == 1) {
        continue;
    }

    print "<option value=\"${player['playerid']}\">${player['name']} - ${player['pos']} - ${player['nflteamid']}</option>";
//    print "${player['name']} - ${player['pos']} - ${player['nflteamid']}<br/>";
}

?>
</select>

<input type="submit"/>
</form>
