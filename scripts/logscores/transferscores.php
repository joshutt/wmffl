<?php
require_once dirname(__FILE__) . '/../base.php';

function catchBadFunction($errno, $errstr, $errfile, $errline, &$vars)
{
    error_log("$errstr in $errfile on line $errline");
    if ($errno == FATAL || $errno == ERROR) {
        exit(1);
    }
//    print_r ($vars);
    print 'Problem with player: ' . $vars['player']['playerid'];
    print ' - Pos: ' . $vars['player']['position'] . "\n";
    $vars['pts'] = 0;
}

include 'base/scoring.php';

//$week = $currentWeek - 1;
$week = $currentWeek;

$sql = 'select p.playerid, p.pos, s.season, s.* from newplayers p, stats s ';

$bigquery = 'insert into playerscores (playerid, season, week, pts) ';
$bigquery .= 'values ';

$results = mysqli_query($conn, $sql) or die('Dead on select: ' . mysqli_error($conn));
$first = 1;
set_error_handler('catchBadFunction');
while ($player = mysqli_fetch_array($results)) {
    $funcName = 'score' . $player['pos'];
    if ($player['pos'] == '') {
        $pts = 0;
    } else {
        $pts = call_user_func($funcName, $player);
    }

    if ($first != 1) {
        $bigquery .= ', ';
    }
    $first = 0;
    $bigquery .= '(' . $player['playerid'] . ', ' . $player['season'] . ', ';
    $bigquery .= $player['week'] . ', ' . $pts . ') ';

}
restore_error_handler();
print $bigquery;
mysqli_query($conn, $bigquery) or die('Error: ' . mysqli_error($conn));


$querySql = <<<EOD
    UPDATE playerscores ps
    JOIN newplayers p ON ps.playerid=p.playerid
    JOIN revisedactivations a ON p.playerid=a.playerid AND a.season=ps.season AND a.week=ps.week
    SET ps.active=ps.pts
    WHERE ps.season=$currentSeason AND ps.week=$week
EOD;

$results = mysqli_query($conn, $querySql) or die('Error: ' . mysqli_error($conn));

print 'Successfully Transfered ' . mysqli_affected_rows($conn) . " scores\n";

?>
