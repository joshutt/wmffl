<?
require_once "$DOCUMENT_ROOT/base/conn.php";
if (isset($HTTP_COOKIE_VARS["teamid"]) && isset($HTTP_COOKIE_VARS["user"])) {
	$thequery = "select teamid, password, userid from user where password='".$HTTP_COOKIE_VARS["teamid"]."' and username='".$HTTP_COOKIE_VARS["user"]."'";
	$result = mysql_query($thequery, $conn);
	$numrow = mysql_num_rows($result);

	if ($numrow == 0) {
		$teamnum = 0;
	} else {
		$team = mysql_fetch_row($result);
		$teamnum = $team[0];
        $usernum = $team[2];
//		setcookie ("teamid", $team[1], 0, "/", ".wmffl.com");
//		setcookie ("user", $user, 0, "/", ".wmffl.com");
	}
//	putenv("TEAMNUM=".$teamnum);
//	setcookie ("teamnum", $teamnum, 0, "/", ".wmffl.com");	
	$isin = true;
} else {
	$isin = false;
}

$currentseason=2001;

?>
