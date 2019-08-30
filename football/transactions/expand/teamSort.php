<?
require_once "utils/start.php";


$orderBy = $_REQUEST["sort"];

$sql = <<<EOD
select ex.playerid, t.name as 'teamid', concat(ex.firstname, ' ', ex.lastname) as 'name', ex.pos, if (el.num>=2 or exp.teamid is not null, 1, ep.protected) as 'protected', ex.cost, r.nflteamid
from expandAvailable ex
join team t on ex.teamid=t.name
join expansionLost el on t.teamid=el.teamid
left join expansionprotections ep on ex.playerid=ep.playerid
left join nflrosters r on ex.playerid=r.playerid and r.dateoff is null
left join expansionpicks exp on exp.playerid=ex.playerid
    order by `$orderBy`, pos, lastname
EOD;

$results = mysqli_query($conn, $sql) or die("Unable to get expansion protections: " . mysqli_error($conn));

$currentTeam = "";
$count =0;
print "<table class=\"SLTables1 left\" id=\"tblId\">";
while ($player = mysqli_fetch_array($results)) {
    if ($player['protected'] == 1) {
        $class = 'protect';
    } else {
        $class = 'available';
    }

    if ($player[$orderBy] !=  $currentTeam) {
        $currentTeam = $player[$orderBy];
        $count++;

        if ($count == 6) {
            print "</table>";
            print "<table class=\"SLTables1 left\">";
        }
        print "<tr class=\"bg1\"><th colspan=\"4\">${player[$orderBy]}</th></tr>";
    }

    print "<tr class=\"bg2 $class\"><td>${player['name']}</td><td>${player['pos']}</td><td>${player['nflteamid']}</td><td>${player['cost']}</td></tr>";
} 
print "</table>";
?>
