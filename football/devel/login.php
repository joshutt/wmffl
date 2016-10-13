<?
	if ((isset ($valid)) || ((isset ($Password)) && ($Password == "test"))) {
		echo ("Logged in");
	}
	echo $User;
	echo $Password;

	
//	setcookie("loggedin", "false", time()+3600);
	// get the user variable then cookie to see if they are logged in
	echo $loggedin;
	
	// check if logged in
	if ((isset ($loggedin)) && ($loggedin == "true")) {
		// display the logged in version
		echo ("Logged in");
	} else {
		if (isset ($WMFFLUser)) {
			// fill in user name
			echo ("Not logged in");
		} else {
			echo ("Nothing set");
		}
	}
	
	
	
?>