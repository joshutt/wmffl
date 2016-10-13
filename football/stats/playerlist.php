<?
include "base/conn.php";
include "base/useful.php";

header("Content-type: text/plain");

$sql = "SELECT p.lastname, p.firstname, p.pos, p.team, ps.week, ps.pts
FROM  playerscores ps, newplayers p
WHERE ps.season =$currentSeason AND ps.playerid = p.playerid AND p.status='A'
ORDER  BY  ps.week, p.position, ps.pts DESC, p.lastname, p.firstname";

print "Last Name,First Name,Pos,NFL,Week,Pts\n";
$results = mysql_query($sql) or die("There was an error in the query: ".mysql_error());
while($playList = mysql_fetch_array($results)) {
    print $playList[0].",".$playList[1].",".$playList[2].",";
    print $playList[3].",".$playList[4].",".$playList[5];
    print "\n";
}
?>
