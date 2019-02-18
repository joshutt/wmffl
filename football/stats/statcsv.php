<?
#header("Content-type: text/txt");
header("Content-type: text/csv");

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
    $season = $currentSeason;
} else {
    $season = $_REQUEST["season"];
}

if ($_REQUEST["startWeek"] == null || $_REQUEST["startWeek"] == "") {
    $startWeek = 1;
} else {
    $startWeek = $_REQUEST["startWeek"];
}
if ($_REQUEST["endWeek"] == null || $_REQUEST["endWeek"] == "") {
    $endWeek = 17;
} else {
    $endWeek = $_REQUEST["endWeek"];
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
'HC' => array('if(s.ptdiff>0,1,0)', 's.ptdiff')
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
'HC' => array('Wins', 'Pt Diff')
);

$posName = array(
'QB' => 'Quarterback', 'RB' => 'Runningback', 'WR' => 'Wide Receiver',
'TE' => 'Tight End', 'K' => 'Kicker', 'OL' => 'Offensive Line',
'DL' => 'Defensive Line', 'LB' => 'Linebacker', 'DB' => 'Defensive Back',
'HC' => 'Head Coach');

$sql = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, p.team, 
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
WHERE ps.season =$season
AND p.pos='$pos'
AND p.usepos=1
AND ps.week >= $startWeek AND ps.week <= $endWeek
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

$buildString =  "{$posName[$pos]}, Name, NFL Team, FF Team, Games, Pts, PPG"; 


$pLab = $posLabels[$pos];
foreach ($pLab as $pl) {
    $sortVal = $pLine[array_search($pl, $pLab)];
    $buildString .= ", $pl";
}
print "$buildString\n";

//print "Last Name,First Name,Pos,NFL,Week,Pts\n";
//print $sql;

$results = mysql_query($sql) or die("There was an error in the query: ".mysql_error());
while($playList = mysql_fetch_array($results)) {
    //print $playList[0].",".$playList[1].",".$playList[2].",";
    //print $playList[3].",".$playList[4].",".$playList[5];
    //print "\n";
    $buildString = "{$playList["name"]}, {$playList["nflteam"]}, "; 
    $buildString .= "{$playList["ffteam"]}, ";
    $buildString .= "{$playList["games"]}, {$playList["pts"]}, ";
    $buildString .= "{$playList["ppg"]}";

    for ($i = 6; $i<sizeof($pLine)+6; $i++) {
        $buildString .= ", {$playList[$i]}";
    }
    print "$buildString\n";
}
?>
