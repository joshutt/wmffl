<?
require_once "utils/start.php";
$title = "Player Stats";

if ($_REQUEST["pos"] == null || $_REQUEST["pos"]=="") {
    $pos = "QB";
} else {
    $pos = $_REQUEST["pos"];
}

if ($_REQUEST["sort"] == null || $_REQUEST["sort"] == "") {
    $sort = "ppg";
} else {
    $sort = $_REQUEST["sort"];
}

if ($_REQUEST["season"] == null || $_REQUEST["season"] == "") {
    if ($currentWeek == 0) {
        $season = $currentSeason - 1;
    } else {
        $season = $currentSeason;
    }
} else {
    $season = $_REQUEST["season"];
}


$posMap = array(
'QB' => array('s.yards', 's.tds', 's.intthrow', 's.fum', 's.2pt'),
'RB' => array('s.yards', 's.rec', 's.tds', 's.fum', 's.2pt', 's.specTD'),
'WR' => array('s.yards', 's.rec', 's.tds', 's.fum', 's.2pt', 's.specTD'),
'TE' => array('s.yards', 's.rec', 's.tds', 's.fum', 's.2pt', 's.specTD'),
'K' => array('s.XP', 's.MissXP', 's.FG30', 's.FG40', 's.FG50', 's.FG60', 's.MissFG30', 's.2pt', 's.specTD'),
'OL' => array('s.yards', 's.sacks', 's.tds'),
'DL' => array('s.tackles', 's.passdefend', 's.sacks', 's.intcatch', 's.fumrec', 's.forcefum', 's.returnyards', 's.Safety', 's.tds', 's.specTD'),
'LB' => array('s.tackles', 's.passdefend', 's.sacks', 's.intcatch', 's.fumrec', 's.forcefum', 's.returnyards', 's.Safety', 's.tds', 's.specTD'),
'DB' => array('s.tackles', 's.passdefend', 's.sacks', 's.intcatch', 's.fumrec', 's.forcefum', 's.returnyards', 's.Safety', 's.tds', 's.specTD'),
'HC' => array('if(s.ptdiff>0,1,0)', 's.ptdiff', 's.penalties')
);

$posLabels = array(
'QB' => array('Yards', 'TDs', 'INT', 'Fumbles', '2pt'),
'RB' => array('Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'),
'WR' => array('Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'),
'TE' => array('Yards', 'Rec', 'TDs', 'Fumbles', '2pt', 'Special TDs'),
'K' => array('XP', 'Miss XP', 'FG 0-39', 'FG 40-49', 'FG 50-59', 'FG 60+', 'Miss FG 0-30', '2pt', 'Special TDs'),
'OL' => array('Yards', 'Sacks', 'TDs'),
'DL' => array('T', 'PD', 'Sck', 'INT', 'FR', 'FF', 'Ret Yds', 'Safety', 'TDs', 'Spec TDs'),
'LB' => array('T', 'PD', 'Sck', 'INT', 'FR', 'FF', 'Ret Yds', 'Safety', 'TDs', 'Spec TDs'),
'DB' => array('T', 'PD', 'Sck', 'INT', 'FR', 'FF', 'Ret Yds', 'Safety', 'TDs', 'Spec TDs'),
'HC' => array('Wins', 'Pt Diff', 'Pen')
);

$posName = array(
'QB' => 'Quarterback', 'RB' => 'Runningback', 'WR' => 'Wide Receiver',
'TE' => 'Tight End', 'K' => 'Kicker', 'OL' => 'Offensive Line',
'DL' => 'Defensive Line', 'LB' => 'Linebacker', 'DB' => 'Defensive Back',
'HC' => 'Head Coach');

$sql = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, p.team, b.week as 'bye',
t.abbrev as 'ffteam',
sum(if(s.played>0,1,0)) as 'games',
sum(ps.pts) as 'pts',
EOD;

$pLine = $posMap[$pos];
foreach ($pLine as $pset) {
    $sql .= "sum($pset), ";
}

$sql .= <<<EOD
round(sum(ps.pts)/sum(if(s.played>0,1,0)), 2) as 'ppg'
FROM  playerscores ps
JOIN newplayers p ON ps.playerid=p.playerid
JOIN stats s ON s.statid=p.flmid AND s.season=ps.season AND s.week=ps.week
LEFT JOIN roster r ON r.playerid=p.playerid AND r.dateoff is null
LEFT JOIN team t ON t.teamid=r.teamid
LEFT JOIN nflbyes b ON p.team=b.nflteam AND ps.season=b.season
WHERE ps.season =$season
AND p.pos='$pos'
AND p.usepos=1
GROUP BY p.playerid
ORDER BY `$sort` DESC, `pts` DESC
EOD;

$firstSort = $_POST["firstsort"];
if (isset($firstSort) && $firstSort != "none") {
    $sql .= "ORDER BY $firstSort ";
    $secondSort = $_POST["secondsort"];
    if (isset($secondSort) && $secondSort != "none") {
        $sql .= ", $secondSort ";
    }
}
//ORDER  BY  ps.week, p.position, ps.pts DESC, p.lastname, p.firstname";
?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="/base/vendor/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $('#statTable').tablesorter(); 
});
</script>

<? include "base/menu.php"; ?>


<style>
<!--
.SLTables1, .SLTables1 TD, .SLTables1 TH, FORM {font-size:10px; font-family:Verdana, Geneva, Arial, Helvetica, sans-serif;}
.bg0 {background-color:#660000; font-weight:bold; color:#e2a500;}
.bg1 {background-color:#B9B9B9; font-weight:bold;}
.bg2 {background-color:#f5efef;}
.bg0font, .bg0font A, A.bg0font {color:#FFFFFF; font-weight:bold;}
-->
</style>

<h1 align="center">Player Stats</h1>
<hr/>
<? include "base/statbar.html";?>

<p><table width="100%">
<tr>
<td><a href="playerstats.php?pos=HC">HC</a></td>
<td><a href="playerstats.php?pos=QB">QB</a></td>
<td><a href="playerstats.php?pos=RB">RB</a></td>
<td><a href="playerstats.php?pos=WR">WR</a></td>
<td><a href="playerstats.php?pos=TE">TE</a></td>
<td><a href="playerstats.php?pos=K">K</a></td>
<td><a href="playerstats.php?pos=OL">OL</a></td>
<td><a href="playerstats.php?pos=DL">DL</a></td>
<td><a href="playerstats.php?pos=LB">LB</a></td>
<td><a href="playerstats.php?pos=DB">DB</a></td>
</tr>
</table></p>
<!--
<form action="playerstats.php" method="post">
<table>

<tr><td align="right"><b>Position:</b></td>
<td>
<select name="pos">
    <option value="HC">Head Coach</option>
    <option value="QB">Quarterback</option>
    <option value="RB">Runningback</option>
    <option value="WR">Wide Receiver</option>
    <option value="TE">Tight End</option>
    <option value="K">Kicker</option>
    <option value="OL">Offensive Line</option>
    <option value="DL">Defensive Line</option>
    <option value="LB">Linebacker</option>
    <option value="DB">Defensive Back</option>
</select></td></tr>

<tr><td align="center" colspan="2"><input type="submit" value="Get Stats"/></td></tr>
</table>
</form>
<hr/>

-->

<div class="SLTables1">
<table border="0" cellpadding="2" cellspacing="1" width="100%" id="statTable">
<thead>
<tr align="left" class="bg0"><td colspan="<? print sizeof($pLine)+7; ?>" class="bg0">
<font class="bg0font"><? print $posName[$pos]; ?></font></td></tr>
<tr align="middle" class="bg1"><th>Name</th>
<th>NFL Team</th>
<th>Bye</th>
<th>FF Team</th>
<th>Games</th>
<th>Pts</th>
<th>PPG</th>
<?
$pLab = $posLabels[$pos];
foreach ($pLab as $pl) {
    $sortVal = $pLine[array_search($pl, $pLab)];
    print "<th>$pl</td>";
}
print "</tr></thead><tbody>";

//print "Last Name,First Name,Pos,NFL,Week,Pts\n";
$results = mysqli_query($conn, $sql) or die("There was an error in the query: " . mysqli_error($conn));
while ($playList = mysqli_fetch_array($results)) {
    //print $playList[0].",".$playList[1].",".$playList[2].",";
    //print $playList[3].",".$playList[4].",".$playList[5];
    //print "\n";
    $numGames = $playList["games"];
    if ($numGames == 0) {$numGames=1;}
    $ppg = round($playList["pts"]/$numGames,2);
    print <<<EOD
<tr height="17" class="bg2" align="right" valign="middle">
<td align="left">{$playList["name"]}</td>
<td align="center">{$playList["team"]}</td>
<td align="center">{$playList["bye"]}</td>
<td align="center">{$playList["ffteam"]}</td>
<td align="center">{$playList["games"]}</td>
<td align="center">{$playList["pts"]}</td>
<td align="center">{$playList["ppg"]}</td>
EOD;

    for ($i = 7; $i<sizeof($pLine)+7; $i++) {
        print "<td align=\"center\">{$playList[$i]}</td>";
    }
}
?>
</tbody>
</table>
</div>

<p><div align="right"><a href="statcsv.php?pos=<? print "$pos&sort=$sort&season=$season";?>"><img src="/images/csv.gif" border="0"></a></div><p>

<? include "base/footer.php"; ?>
