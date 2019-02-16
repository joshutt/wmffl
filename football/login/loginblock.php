<?
require_once "base/conn.php";

if (isset($teamid)) {
	$thequery = "select teamid, password from user where password='".$teamid."'";
	$result = mysql_query($thequery, $conn);
	$numrow = mysql_num_rows($result);

	if ($numrow == 0) {
		$teamnum = 0;
	} else {
		$team = mysql_fetch_row($result);
		$teamnum = $team[0];
		setcookie ("teamid", $team[1], time()+1800, "/", ".wmffl.com");
		print $teamid;
	}
	
}
?>

<P><FONT Color="Red"><B><? print $message; ?> </B></FONT></P>

<?
	if (isset($teamnum) && $teamnum != 0) {
		
?>
	<P>You are logged in<BR>
	<A HREF="logout.php">Log Out</A></P>
<?
	} else {
?>
	<FORM ACTION="login.php" METHOD="POST">
	Username: <INPUT TYPE="text" NAME="username" size=10><BR>
	Password: <INPUT TYPE="password" NAME="password" WIDTH=10><BR>
	<INPUT TYPE="Submit" NAME="Log In">
	</FORM>
<?
	}
?>
