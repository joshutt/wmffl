<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$protect = array();
$pullback = array();
$alternate = array();

$sql = <<<EOD
select ex.playerid, t.name as 'teamid', concat(ex.firstname, ' ', ex.lastname) as 'name', ex.pos, if (el.num>=2 or exp.teamid is not null, 1, ep.protected) as 'protected', ex.cost, r.nflteamid
from expandAvailable ex
join team t on ex.teamid=t.name
join expansionLost el on t.teamid=el.teamid
left join expansionprotections ep on ex.playerid=ep.playerid
left join nflrosters r on ex.playerid=r.playerid and r.dateoff is null
left join expansionpicks exp on exp.playerid=ex.playerid
    order by ex.teamid, pos, lastname
EOD;

$results = mysql_query($sql) or die("Unable to get expansion protections: ".mysql_error());


$title = "Expansion Picks";
?>

<link href="expandPick.css" type="text/css" rel="stylesheet" />

<script language="javascript" src="expandPick.js"></script>

<body onLoad="load();">
<?
include "$DOCUMENT_ROOT/base/menu.php";
?>

<h1 align="center"><?= $title; ?></h1>
<hr size="1"/>

<p class="text">Players in <span class="gshort">gold</span> are protected or already picked players.  Players in <span class="bshort">burgundy</span> are available to be picked.</p>

<div id="instruct">
<div id="protectShow" onClick="hide()">Hide Protected</div>
<div id="posSort" onClick="sort()">Sort By Position</div>
</div>

<div class="clearLine"></div>

<div id="tableList">
<table class="SLTables1 left" id="tblId">
<?
$currentTeam = "";
$count =0;
while ($player = mysql_fetch_array($results)) {
    if ($player['protected'] == 1) {
        $class = 'protect';
    } else {
        $class = 'available';
    }

    if ($player['teamid'] !=  $currentTeam) {
        $currentTeam = $player['teamid'];
        $count++;

        if ($count == 6) {
            print "</table>";
            print "<table class=\"SLTables1 left\">";
        }
        print "<tr class=\"bg1\"><th colspan=\"4\">${player['teamid']}</th></tr>";
    }

    print "<tr class=\"bg2 $class\"><td>${player['name']}</td><td>${player['pos']}</td><td>${player['nflteamid']}</td><td>${player['cost']}</td></tr>";
} 
?>
</table>
</div>


<?

$sql = <<<EOD
    SELECT concat(p.firstname, ' ', p.lastname) as 'name', p.pos, nr.nflteamid, t.name as 'tname', ex.cost, ep.round
    FROM team t
    LEFT JOIN roster r on r.teamid=t.teamid and r.dateoff is null
    LEFT JOIN newplayers p on r.playerid=p.playerid
    LEFT JOIN nflrosters nr on p.playerid=nr.playerid and nr.dateoff is null
    LEFT JOIN expandAvailable ex on ex.playerid=r.playerid
    LEFT JOIN expansionpicks ep on ep.playerid=r.playerid
    WHERE t.teamid in (12, 13)
    ORDER BY t.teamid, p.pos, p.lastname
EOD;

$results = mysql_query($sql) or die("Unable to get expansion rosters: ".mysql_error());
?>

<div id="rosterList">
<table class="SLTables1">

<?
$currentTeam = "";
while ($player = mysql_fetch_array($results)) {
    if ($player['tname'] != $currentTeam) {
        print "<tr></tr>";
        print "<tr class=\"bg1\"><th colspan=\"4\">${player['tname']}</th></tr>";
        $currentTeam = $player['tname'];
    }

    print "<tr class=\"bg2 available\"><td>${player['name']}</td><td>${player['pos']}</td><td>${player['nflteamid']}</td><td>${player['cost']}</td></tr>";

}
?>

</table>
</div>

<div class="clearLine"></div>

<?

include "$DOCUMENT_ROOT/base/footer.html";
?>
