<?
	require_once "conn.php";

	$thequery = "SELECT UNIX_TIMESTAMP(ActivationDue-INTERVAL 30 MINUTE)-UNIX_TIMESTAMP(), weekname FROM weekmap WHERE now() BETWEEN StartDate AND EndDate";

$results = mysqli_query($conn, $thequery);
$row = mysqli_fetch_row($results);
	$event = $row[1]." Activations due in  ";
	$eventPast = $row[1]." Activations are Past Due";
//	$eventPath = "The Draft is Taking Place Now";

	$diff = $row[0];

//	$event = "Protections due in ";
	putenv("TZ=US/Eastern");
	//$diff = mktime(23,59,59,8,11,2002) - time();
//	$diff = mktime(13,0,0,8,18,2002) - time();
    $daywk = date("w");

	if ($diff > 0 && $daywk != 2) {
	//if ($diff > 0 ) {
		$day = floor($diff/60/60/24);
		$hours = floor($diff/60/60) - $day*24;
		$minutes = floor($diff/60) - $hours*60 - $day*24*60;

		print "<I><FONT size=3 COLOR=red><B>";
		echo "$event $day days, $hours hours, $minutes minutes";
	//		echo "$day days, $hours hours, $minutes minutes till the draft";
		print "</B></FONT></I>";
	} else {
//		echo "$eventPast";
//		echo "</B></FONT></I>";
		include "scores.php";
		//echo "<A HREF=\"activate/currentscore.php\">Scores in Progress</A>";
//		echo "<I><font size=\"3\" COLOR=\"red\"><B>";

	}

?>
