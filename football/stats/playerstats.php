<?
require_once "utils/connect.php";
include "utils/reportUtils.php";
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
SELECT p.playerid, CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, p.team, b.week as 'bye',
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

$firstSort = $HTTP_POST_VARS["firstsort"];
if (isset($firstSort) && $firstSort != "none") {
    $sql .= "ORDER BY $firstSort ";
    $secondSort = $HTTP_POST_VARS["secondsort"];
    if (isset($secondSort) && $secondSort != "none") {
        $sql .= ", $secondSort ";
    }
}
//ORDER  BY  ps.week, p.position, ps.pts DESC, p.lastname, p.firstname";

$javascriptList = array("//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js", "/base/js/jquery.tablesorter.min.js", "week.js");
$cssList = array("week.css");
?>

<? include "base/menu.php"; ?>


<h1 align="center">Player Stats</h1>
<hr/>
<? include "base/statbar.html";?>

<p><table width="100%">
<tr>
    <td><a href="playerstats?pos=HC">HC</a></td>
    <td><a href="playerstats?pos=QB">QB</a></td>
    <td><a href="playerstats?pos=RB">RB</a></td>
    <td><a href="playerstats?pos=WR">WR</a></td>
    <td><a href="playerstats?pos=TE">TE</a></td>
    <td><a href="playerstats?pos=K">K</a></td>
    <td><a href="playerstats?pos=OL">OL</a></td>
    <td><a href="playerstats?pos=DL">DL</a></td>
    <td><a href="playerstats?pos=LB">LB</a></td>
    <td><a href="playerstats?pos=DB">DB</a></td>
</tr>
</table></p>

<?
//print "Last Name,First Name,Pos,NFL,Week,Pts\n";
$newHold = array();
$results = mysqli_query($conn, $sql) or die("There was an error in the query: " . mysqli_error());
while ($playList = mysqli_fetch_array($results)) {
    //print $playList[0].",".$playList[1].",".$playList[2].",";
    //print $playList[3].",".$playList[4].",".$playList[5];
    //print "\n";
    $numGames = $playList["games"];
    if ($numGames == 0) {$numGames=1;}
    $ppg = round($playList["pts"]/$numGames,2);
    $id = $playList["playerid"];
    $newHold[$id] = array($playList["name"], $playList["team"], $playList["bye"], $playList["ffteam"], $numGames, $playList["pts"], $ppg);

    for ($i = 7; $i<sizeof($pLine)+7; $i++) {
        array_push($newHold[$id], $playList[$i]);
    }
}
?>

<?php
$labels = array("Name", "NFL Team", "Bye", "FF Team", "G", "Pts", "PPG");
$labels = array_merge($labels, $posLabels[$pos]);

print "<div id=\"tblblock\">";
print "<div id=\"mainTable\">";
outputHtml($labels ,$newHold);
print "</div></div>";
?>


<? include "base/footer.html"; ?>
