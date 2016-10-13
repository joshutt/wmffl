<?
include "$DOCUMENT_ROOT/utils/start.php";

function make_password($length, $strength=0) {
    $vowels = 'aeiouy';
    $consonants = 'bdghjlmnpqrstvwxz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= 'AEIOUY';
    }
    if ($strength & 4) {
        $consonants .= '0123456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%^';
    }
    $password = '';
    $alt = time() % 2;
    srand(time());
    for ($i=0; $i<$length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}

if (isset($change)) {
    $theQuery = "SELECT name, email FROM user WHERE username='$username'";
    $result = mysql_query($theQuery);
    $numrow = mysql_num_rows($result);
    if ($numrow == 0) {
        $errorMsg = "Invalid Account";
    } else {
        list($name, $email) = mysql_fetch_row($result);
        $newPass = make_password(6, 7);

        // Save the password in DB
        $theQuery = "UPDATE user SET password=PASSWORD('$newPass') WHERE username='$username'";
        $result = mysql_query($theQuery);

        // Send confirmation email
        $body = "$name,

Your request for a new password has been completed.  Your new password is $newPass

This password must be entered exactly as it appears, the login is case sensitive.

Once you are logged in, please change your password to something you will remember.  Thank you.


Webmaster WMFFL";

        mail($email, "WMFFL New Password", $body, "From: webmaster@wmffl.com");
        header("Location: thanksnew.php");
        exit();
    }
}


$title = "Generate New Password";
?>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<h1 align="center"><? print $title; ?></h1>
<hr size="1"/>

<p><font color="red" size="+1" align="center"><? print $errorMsg; ?></font></p>

<form method="post">
    <input type="hidden" name="change" value="true"/>
    <table>
        <tr>
            <td>Username:</td>
            <td><input type="text" name="username" value="<? print $user; ?>"/></td>
        </tr>

        <tr>
            <td colspan="2" align="center"><input type="submit" value="Get New Password"/></td>
        </tr>
    </table>
</form>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
