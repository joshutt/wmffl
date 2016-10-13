<?
require_once "$DOCUMENT_ROOT/base/conn.php";
	if (isset($Username) && isset($OldPassword) && isset($NewPassword) && isset($NewPassword2)) {
		if ($HTTP_COOKIE_VARS["user"] != $Username)
			$ErrorMessage = "You can only change password for your account";
		else if ($NewPassword != $NewPassword2)
			$ErrorMessage = "New Passwords did not match";
		else if ($OldPassword == $NewPassword)
			$ErrorMessage = "Can Not set New Password to Current Password";
		else {
			$ErrorMessage = "Success";
			
			// Make sure that old password matches password passed in
			$thequery = "select name, email from user where username='".$Username."' and password=password('".$OldPassword."')";
			$result = mysql_query($thequery, $conn);
			$numrow = mysql_num_rows($result);
			$email = mysql_fetch_row($result);
			
			if ($numrow == 0) {
				$ErrorMessage = "Old Password does not equal Current Password";
			} else {
				$thequery = "UPDATE user SET password=password('".$NewPassword."') WHERE username='".$Username."'";
				$result = mysql_query($thequery, $conn);
				
				$body = $email[0].",\n\nYour password has been changed as you requested.  ";
				$body = $body."Please remember that your password is case sensitive.  ";
				$body = $body."If you ever forget your password you may have one generated for you automaticly.  Thank you.\n\n\n";
				$body = $body."Webmaster WMFFL";

				// Save password in db
				// Sent confirmation mail
				mail ($email[1], "Notice of Password Change", $body, "From: webmaster@$SERVER_NAME");
				mysql_close($conn);
				header("Location: thankschange.php");
			}
			
			mysql_close($conn);
		}
	}
	
?>

<HTML>
<HEAD>
<TITLE>Change Password</TITLE>
</HEAD>

<? include $DOCUMENT_ROOT."/base2/menu.html"; ?>

<H1 ALIGN=Center>Change WMFFL Password</H1>
<HR size = "1">
<P>
<CENTER>

<P><FONT COLOR="Red" SIZE="+1"><? print $ErrorMessage; ?></FONT></P>

<FORM METHOD="POST">

<TABLE>
<TR><TD>Username:</TD><TD><INPUT TYPE="Text" NAME="Username" VALUE="<? print $HTTP_COOKIE_VARS["user"]; ?>"></TD></TR>
<TR><TD>Old Password:</TD><TD> <INPUT TYPE="Password" NAME="OldPassword"></TD></TR>
<TR><TD>New Password:</TD><TD> <INPUT TYPE="Password" NAME="NewPassword"></TD></TR>
<TR><TD>Retype New Password:</TD><TD> <INPUT TYPE="Password" NAME="NewPassword2"></TD></TR>
<TR><TD></TD><TD><INPUT TYPE="Submit" VALUE="Change Password"></TD></TR>
</TABLE>

</FORM>

</CENTER>
<P>

<? include $DOCUMENT_ROOT."/base2/footer.html"; ?>

