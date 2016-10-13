<?
	putenv("TZ=US/Eastern");

	$event = "Protections Due in ";
	$eventPast = "Protections are Past Due";
	$dateInt = mktime(24,0,0,8,18,2001);

	$diff =  $dateInt - time();
	
	if ($diff > 0) {
		$day = floor($diff/60/60/24);
		$hours = floor($diff/60/60) - $day*24;
		$minutes = floor($diff/60) - $hours*60 - $day*24*60;

		echo "$event $day days, $hours hours, $minutes minutes";
	} else {
		echo "$eventPast";
	}

?>