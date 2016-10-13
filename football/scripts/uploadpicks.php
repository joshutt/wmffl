<?
require "/home/joshutt/football/base/conn.php";

function addToDB($playerid, $teamName, $conn) {
	$sql = "INSERT INTO roster (playerid, teamid, dateon) ";
	//$sql .= "SELECT p.playerid, t.teamid, '2003-08-24' ";
	$sql .= "SELECT p.playerid, t.teamid, '2006-08-26 13:00:00' ";
    $sql .= "FROM team t, players p ";
	$sql .= "WHERE t.name='$teamName' and p.statid='$playerid'";
	print $sql."\n";
	mysql_query($sql, $conn) or die("error: ".mysql_error());
}

$draftFile = fopen("/home/joshutt/football/scripts/draftupload.csv", "r");
while ($data = fgetcsv($draftFile, 1000, ',')) {
	addToDB($data[2], $data[1], $conn);
}
mysql_close($conn);
fclose($draftFile);

?>
