<?
//require_once "base/conn.php";
//require_once "/home/wmffl/public_html/base/conn.php";
#require_once "/home/wmff/public_html/utils/start.php" or die("Dead");

$ini = parse_ini_file("wmffl.conf");
$conn = mysqli_connect('localhost', $ini['userName'], $ini['password'], $ini['dbName']);

$dateQuery = "SELECT w1.season, w1.week, w1.weekname, w2.weekname as 'previous' FROM weekmap w1, weekmap w2 ";
$dateQuery .= "WHERE now() BETWEEN w1.startDate and w1.endDate ";
$dateQuery .= "and IF(w1.week=0, w2.season=w1.season-1 and w2.week=16, w2.week=w1.week-1 and w2.season=w1.season) ";
$dateResult = mysqli_query($conn, $dateQuery);
list($currentSeason, $currentWeek, $weekName, $previousWeekName) = mysqli_fetch_row($dateResult);
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

$number = mysqli_query($conn, $theQuery) or die("Failure: " . mysqli_error($conn));

print "Success: $number";
?>
