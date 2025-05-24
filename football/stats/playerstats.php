<?php
require_once 'utils/connect.php';
include 'utils/reportUtils.php';
include_once 'utils/teamList.php';
$title = 'Player Stats';

if (!isset($_REQUEST['pos']) || $_REQUEST['pos'] == '') {
    $pos = 'QB';
} else {
    $pos = $_REQUEST['pos'];
}

if (!isset($_REQUEST['sort']) || $_REQUEST['sort'] == '') {
    $sort = 'ppg';
} else {
    $sort = $_REQUEST['sort'];
}

if (!isset($_REQUEST['season']) || $_REQUEST['season'] == '') {
    if ($currentWeek == 0) {
        $season = $currentSeason - 1;
    } else {
        $season = $currentSeason;
    }
} else {
    $season = (int) $_REQUEST['season'];
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

$pos = $conn->real_escape_string($pos);
$sort = $conn->real_escape_string($sort);
$season = $conn->real_escape_string($season);

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

if (isset($_POST['firstsort'])) {
    $firstSort = $conn->real_escape_string($_POST['firstsort']);
}

if (isset($firstSort) && $firstSort != 'none') {
    $sql .= "ORDER BY $firstSort ";
    $secondSort = $conn->real_escape_string($_POST['secondsort']);
    if (isset($secondSort) && $secondSort != 'none') {
        $sql .= ", $secondSort ";
    }
}
//ORDER  BY  ps.week, p.position, ps.pts DESC, p.lastname, p.firstname";
//$sql = $conn->real_escape_string($sql);

//print "Last Name,First Name,Pos,NFL,Week,Pts\n";
$newHold = array();
$results = mysqli_query($conn, $sql) or die('There was an error in the query: ' . mysqli_error());
while ($playList = mysqli_fetch_array($results)) {
    //print $playList[0].",".$playList[1].",".$playList[2].",";
    //print $playList[3].",".$playList[4].",".$playList[5];
    //print "\n";
    $numGames = $playList['games'];
    if ($numGames == 0) {$numGames=1;}
    $ppg = round($playList['pts']/$numGames,2);
    $id = $playList['playerid'];
    $newHold[$id] = array($playList['name'], $playList['team'], $playList['bye'], $playList['ffteam'], $numGames, $playList['pts'], $ppg);

    foreach ($posMap[$pos] as $pset) {
        array_push($newHold[$id], $playList["sum($pset)"]);
    }

}

// Get the labels
$labels = array('Name', 'NFL Team', 'Bye', 'FF Team', 'G', 'Pts', 'PPG');
$labels = array_merge($labels, $posLabels[$pos]);


// Determine the format
$format = 'html';
if (isset($_REQUEST['format'])) {
    $format = strtolower($_REQUEST['format']);
}

// Display output
if ($format == 'html' || !supportedFormat($format)) {
    $javascriptList = array('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js', '/base/vendor/js/jquery.tablesorter.min.js', '/base/js/playerstats.js');
    $cssList = array('/base/css/stats.css');
    include 'base/menu.php'; ?>


    <h1 align="center">Player Stats</h1>
    <hr/>
    <?php include 'base/statbar.html'; ?>

    <div id="tblblock" class="container">

        <div class="formatOptions row col justify-content-center">
            <select id="pos" class="mr-2">
                <?php
                foreach (getPosList() as $setPos) {
                    if ($setPos == $pos) {
                        $selected = "selected=\"true\" ";
                    } else {
                        $selected = '';
                    }
                    print "<option value=\"$setPos\" $selected>$setPos</option>";
                }
                ?>
            </select>
            <button class="button mx-1" id="csv" onClick="csv()">CSV</button>
            <button class="button mx-1" id="json" onClick="csv('json')">JSON</button>
        </div>
        <div id="mainTable" class="row col-12 justify-content-center">
            <?php outputHtml($labels, $newHold); ?>
        </div>
    </div>


    <?php include 'base/footer.php'; ?>


    <?php
} else if ($format == 'csv') {
    outputCSV($labels, $newHold, "playerstats-$pos.csv");
} else if ($format == 'json') {
    outputJSON($labels, $newHold);
} else if ($format == 'ajax') {
    outputHtml($labels, $newHold);
}
?>