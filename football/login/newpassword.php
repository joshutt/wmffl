<?
require_once "$DOCUMENT_ROOT/utils/start.php";
if (!$isin) {
    header("Location: http://wmffl.com");
    exit();
}

if (isset($change)) {
    if ($user != $username) {
        $errorMessage = "You can only change the password for your account";
    } else if ($newpassword1 != $newpassword2) {
        $errorMessage = "New passwords did not match each other";
    } else if ($oldpassword == $newpassword1) {
        $errorMessage = "Can not set new password to current password";
    } else {
        $errorMessage = "Success";

        // Make sure that the old password matches entered password
        $theQuery = "SELECT name, email FROM user WHERE username='$username' AND (password=PASSWORD('$oldpassword') or password=MD5('$oldpassword'))";
        $result = mysql_query($theQuery);
        $numrow = mysql_num_rows($result);

        if ($numrow != 1) {
            $errorMessage = "Old password does not equal current password";
        } else {
            list($name, $email) = mysql_fetch_row($result);
            
            // Save password in database
            $theQuery = "UPDATE user SET password=MD5('$newpassword1') WHERE username='$user'";
            $result = mysql_query($theQuery) or die("An error occured: ".mysql_error());

            // Send email confirming change
            $body = "$name,

Your password has been changed as you requested.  Please remember that your password is case sensitive.  If you ever forget your password you may have one generated for you automaticly.  Thank you.


Webmaster WMFFL";

            mail($email, "Notice of Password Change", $body, "From: webmaster@wmffl.com");
            header("Location: thankschange.php");
            exit();
        }
    }
}

$title = "Change Password";
?>


<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<h1 align="center">Change WMFFL Password</h1>
<hr size="1"/>

<p><font color="red" size="+1" align="center">
<? print $errorMessage; ?></font></p>

<form method="post" action="newpassword.php">
    <input type="hidden" name="change" value="true"/>
    <table>
        <tr>
            <td>Username:</td>
            <td><input type="text" name="username" value="<? print $user; ?>"/></td>
        </tr>

        <tr>
            <td>Old Password:</td>
            <td><input type="password" name="oldpassword"/></td>
        </tr>

        <tr>
            <td>New Password:</td>
            <td><input type="password" name="newpassword1"/></td>
        </tr>

        <tr>
            <td>Retype New Password:</td>
            <td><input type="password" name="newpassword2"/></td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <input type="submit" value="Change Password"/>
            </td>
        </tr>
    </table>
</form>


<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
