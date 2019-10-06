
<p><table width="145">
<tr><td>

<?
if (isset($message) && $message != "") {
?>
<CENTER>
<P><FONT Color="Red" SIZE="-1"><B><? print $message; ?> </B></FONT></P>
</CENTER>

</TD></TR>
<TR><TD>

<?	
$message = "";
}
if ($isin) {
		
?>
	<CENTER>
	<P><div class="loginText">Welcome <? print $fullname; ?></div></P>
	
	<P><A HREF="/login/logout.php">Log Out</A></P>
<? if ($teamnum == 2) { print "<a href=\"/login/simulatelogin.php\">Commish</a>"; } ?>
	<P><A HREF="/login/newpassword.php">Change Password</A></P>
	</CENTER>
<?
} else {

?>	
	<CENTER>
	<FONT COLOR="#660000" SIZE="-1">
	<FORM ACTION="/login/login.php" METHOD="POST">
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
