<?
require_once "base/conn.php";
session_start();
	$thequery = "select teamid, password, name, username from user where teamid=$teamchangeid";
	$result = mysql_query($thequery, $conn);
	$numrow = mysql_num_rows($result);

	if ($numrow == 0) {
		header("Location: ".$HTTP_REFERER);
		setcookie ("message", "Invalid Username/Password", 0, "/", ".wmffl.com");
	}
	else {
		$team = mysql_fetch_row($result);		
		setcookie ("teamid", $team[0], 0, "/", ".wmffl.com");
		setcookie ("teamnum", $team[0], 0, "/", ".wmffl.com");
		setcookie ("user", $team[3], 0, "/", ".wmffl.com");
		setcookie ("message", "", 0, "/", ".wmffl.com");
		setcookie ("fullname", $team[2], 0, "/", ".wmffl.com");
		//$thequery = "update user set lastlog=now() where username='$username'";
		//$result = mysql_query($thequery, $conn);
		header("Location: http://www.wmffl.com");
	}
?>
