<?
require_once "$DOCUMENT_ROOT/base/conn.php";
	$thequery = "select teamid, password from user where username='".$username."' and password=password('".$password."')";
	$result = mysql_query($thequery, $conn);
	$numrow = mysql_num_rows($result);

	if ($numrow == 0) {
		header("Location: ".$HTTP_REFERER);
		setcookie ("message", "Invalid Username/Password", 0, "/", ".wmffl.com");
	}
	else {
		$team = mysql_fetch_row($result);		
		setcookie ("teamid", $team[1], 0, "/", ".wmffl.com");
		setcookie ("teamnum", $team[0], 0, "/", ".wmffl.com");
		setcookie ("user", $username, 0, "/", ".wmffl.com");
		setcookie ("message", "", 0, "/", ".wmffl.com");
		$thequery = "update user set lastlog=now() where username='$username'";
		$result = mysql_query($thequery, $conn);
		header("Location: ".$HTTP_REFERER);
	}
?>
