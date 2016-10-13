<? 
	setcookie("teamid", "", time()-3600, "/", ".wmffl.com");
	setcookie("user", "", time()-3600, "/", ".wmffl.com");
	setcookie("teamnum", "", time()-3600, "/", ".wmffl.com");
	header("Location: ".$HTTP_REFERER);
?>