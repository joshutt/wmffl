<?
//require_once "$DOCUMENT_ROOT/base/conn.php";
//require_once "/home/wmffl/public_html/base/conn.php";
#require_once "/home/wmff/public_html/utils/start.php" or die("Dead");

$conn = mysql_connect('localhost','joshutt_footbal','wmaccess');
mysql_select_db("joshutt_oldwmffl");

$dateQuery = "SELECT w1.season, w1.week, w1.weekname, w2.weekname as 'previous' FROM weekmap w1, weekmap w2 ";
$dateQuery .= "WHERE now() BETWEEN w1.startDate and w1.endDate ";
$dateQuery .= "and IF(w1.week=0, w2.season=w1.season-1 and w2.week=16, w2.week=w1.week-1 and w2.season=w1.season) ";
$dateResult = mysql_query($dateQuery, $conn);
list($currentSeason, $currentWeek, $weekName, $previousWeekName) = mysql_fetch_row($dateResult);
if ($currentWeek == 0) {
    $previousWeekSeason = $currentSeason-1;
    $previousWeek = 16;
} else {
    $previousWeekSeason = $currentSeason;
    $previousWeek = $currentWeek-1;
}



$oldWeek = $currentWeek - 1;
$season = $currentSeason;

$theQuery = <<<EOD
insert into revisedactivations
select season, week+1, teamid, pos, playerid
from revisedactivations
where season=$season and week=$oldWeek and teamid not in
(select distinct teamid from revisedactivations
where season=$season and week=$currentWeek)
EOD;
print $theQuery;

$number = mysql_query($theQuery) or die("Failure: ".mysql_error());

print "Success: $number";
?>
