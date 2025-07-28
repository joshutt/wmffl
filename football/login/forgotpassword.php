<?php
require_once 'utils/StringUtils.php';
require_once 'utils/start.php';

if (isset($_POST['username'])) {

    try {
        // Make sure that old password matches password passed in
        $thequery = 'select name, email from user where username=?';
        $stmt = mysqli_prepare($conn, $thequery);

        // Bind the username
        $username = $_POST['username'];
        mysqli_stmt_bind_param($stmt, 's', $username);

        mysqli_stmt_bind_result($stmt, $name, $email);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_fetch($stmt);
        $closed = false;
    if (!isset($email)) {
            $ErrorMessage = 'Invalid Account';
        } else {
            mysqli_stmt_close($stmt);
            $newPass = make_password(12, 7);

            // Save password in db
            $thequery = 'UPDATE user SET password=md5(?) WHERE username=?';
            $stmt = mysqli_prepare($conn, $thequery);
            mysqli_stmt_bind_param($stmt, 'ss', $newPass, $username);
            $result = mysqli_stmt_execute($stmt);

            // Sent confirmation mail
            $body = "$name,\n\nYour request for a new password has been completed.  Your new password is ";
            $body .= "$newPass\n\nThis password must be entered exactly as it appears, the login is case sensitive.\n\n";
            $body .= "Once you are logged in, please change your password to something you will remember.  Thank you.\n\n\n";
            $body .= 'Webmaster WMFFL';

            error_log('Mail to: ' . $email);
            error_log('Mail From: webmaster@' . $_SERVER['SERVER_NAME']);
            error_log("Mail Body: $body");
            mail($email, 'WMFFL New Password', $body, 'From: webmaster@' . $_SERVER['SERVER_NAME']);


            // Close things out
            header('Location: thanksnew.php');
        }
    } finally {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}

$title = 'Generate New Password';
include 'base/menu.php';
?>

<h1 class="full">Generate New WMFFL Password</h1>

<div class="justify-content-center m-2 text-center">

    <?php if (isset($ErrorMessage)) { ?>
        <div class="alert alert-danger alert-dismissible" role="alert"><?= $ErrorMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php } ?>

    <form method="post" class="form-signin" action="forgotpassword">
        <div class="username-container col-4 m-3 mx-auto">
            <label for="username" class="sr-only">Username</label>
            <input type="text" name="username" class="form-control"
                   placeholder="<?= $_COOKIE['user'] ?? 'Username' ?>" required
                   autofocus>
        </div>
        <button type="submit" class="btn btn-lg btn-wmffl">Submit</button>
    </form>

<!--    <TABLE>-->
<!--        <TR>-->
<!--            <TD>Username:</TD>-->
<!--            <TD><INPUT TYPE="Text" NAME="Username" VALUE="--><?php //= $_COOKIE["user"] ?? ''; ?><!--"></TD>-->
<!--        </TR>-->
<!--        <TR>-->
<!--            <TD></TD>-->
<!--            <TD><INPUT TYPE="Submit" VALUE="Get New Password"></TD>-->
<!--        </TR>-->
<!--    </TABLE>-->

</div>


<?php include 'base/footer.php'; ?>

