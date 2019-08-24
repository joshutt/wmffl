<?php
require_once "utils/StringUtils.php";
require_once "utils/start.php";

if (isset($Username)) {

    // Make sure that old password matches password passed in
    $thequery = "select name, email from user where username='" . $Username . "'";
    $result = mysqli_query($conn, $thequery);
    $email = mysqli_fetch_row($result);
    $numrow = mysqli_num_rows($result);
    if ($numrow == 0) {
        $ErrorMessage = "Invalid Account";
    } else {

        //$newPass = generate_password(6);
        $newPass = make_password(6, 7);

        // Save password in db
        // Sent confirmation mail
        $thequery = "UPDATE user SET password=md5('" . $newPass . "') WHERE username='" . $Username . "'";
        $result = mysqli_query($conn, $thequery);

        $body = $email[0] . ",\n\nYour request for a new password has been completed.  Your new password is ";
        $body = $body . "" . $newPass . "\n\nThis password must be entered exactly as it appears, the login is case sensitive.\n\n";
        $body = $body . "Once you are logged in, please change your password to something you will remember.  Thank you.\n\n\n";
        $body = $body . "Webmaster WMFFL";

        error_log("Mail to: " . $email[1]);
        error_log("Mail From: webmaster@" . $_SERVER['SERVER_NAME']);
        error_log("Mail Body: $body");
        mail($email[1], "WMFFL New Password", $body, "From: webmaster@" . $_SERVER['SERVER_NAME']);
        mysqli_close($conn);
        header("Location: thanksnew.php");
    }

    mysqli_close($conn);
}

$title = "Generate New Password";
include "base/menu.php";
?>

<H1 ALIGN=Center>Generate New WMFFL Password</H1>
<HR size="1">
<P>
    <CENTER>

        <?php if (isset($ErrorMessage)) { ?>
    <P><FONT COLOR="Red" SIZE="+1"><?= $ErrorMessage ?></FONT></P>
<?php } ?>

<FORM METHOD="POST">

    <TABLE>
        <TR>
            <TD>Username:</TD>
            <TD><INPUT TYPE="Text" NAME="Username" VALUE="<?= $_COOKIE["user"]; ?>"></TD>
        </TR>
        <TR>
            <TD></TD>
            <TD><INPUT TYPE="Submit" VALUE="Get New Password"></TD>
        </TR>
    </TABLE>

</FORM>

</CENTER>
<P>

    <? include "base/footer.html"; ?>

