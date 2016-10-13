<P><TABLE WIDTH=145>
<TR><TD>

<?
if (isset($message)) {
?>
<CENTER>
<P><FONT Color="Red" SIZE="-1"><B><? print $message; ?> </B></FONT></P>
</CENTER>

</TD></TR>
<TR><TD>

<?	
$message = "";
}
if (isset($teamid) && $teamid != 0) {
		
?>
	<CENTER>
	<P><FONT COLOR="Blue"><B>Welcome <? print $user; ?></B></FONT></P>
	
	<P><A HREF="/login/logout.php">Log Out</A></P>
	<P><A HREF="/login/newpassword.php">Change Password</A></P>
	</CENTER>
<?
} else {

?>	
	<CENTER>
	<FONT COLOR="Blue" SIZE="-1">
	<FORM ACTION="/login/login.php" METHOD="POST">
	<? print $teamid; ?>
	Username:<BR><INPUT TYPE="text" NAME="username" size=10><BR>
	Password:<BR> <INPUT TYPE="password" NAME="password" size=10><BR>
	<INPUT TYPE="Submit" VALUE="Log In">
	</FORM>
	</FONT>
	
	<P><A HREF="/login/forgotpassword.php">Forgot Password</A></P>

	</CENTER>
<?
	}
?>

</TD></TR>
</TABLE></P>