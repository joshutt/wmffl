<?php
require_once 'utils/start.php';
if (!$isin) {
    header('Location: http://wmffl.com');
    exit();
}

if (isset($change)) {
    if ($user != $username) {
        $errorMessage = 'You can only change the password for your account';
    } else if ($newpassword1 != $newpassword2) {
        $errorMessage = 'New passwords did not match each other';
    } else if ($oldpassword == $newpassword1) {
        $errorMessage = 'Can not set new password to current password';
    } else {
        $errorMessage = 'Success';

        // Make sure that the old password matches entered password
        $theQuery = "SELECT name, email FROM user WHERE username='$username' AND (password=PASSWORD('$oldpassword') or password=MD5('$oldpassword'))";
        $result = mysqli_query($conn, $theQuery);
        $numrow = mysqli_num_rows($result);

        if ($numrow != 1) {
            $errorMessage = 'Old password does not equal current password';
        } else {
            list($name, $email) = mysqli_fetch_row($result);

            // Save password in database
            $theQuery = "UPDATE user SET password=MD5('$newpassword1') WHERE username='$user'";
            $result = mysqli_query($conn, $theQuery) or die('An error occured: ' . mysqli_error($conn));

            // Send email confirming change
            $body = "$name,

Your password has been changed as you requested.  Please remember that your password is case sensitive.  If you ever forget your password you may have one generated for you automaticly.  Thank you.


Webmaster WMFFL";

            mail($email, 'Notice of Password Change', $body, 'From: webmaster@wmffl.com');
            header('Location: thankschange.php');
            exit();
        }
    }
}

$title = 'Change Password';
?>


<?php include 'base/menu.php'; ?>

<h1 align="center">Change WMFFL Password</h1>
<hr size="1"/>

<p><font color="red" size="+1" align="center">
        <?php print $errorMessage; ?></font></p>

<form method="post" action="newpassword.php">
    <input type="hidden" name="change" value="true"/>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label" for="username">Username:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="username" name="username" value="<?= $user ?>"/>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label" for="oldpassword">Old Password:</label>
        <div class="col-sm-10">
            <input type="password" class="form-control" id="oldpassword" name="oldpassword" />
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label" for="newpassword1">New Password:</label>
        <div class="col-sm-10">
            <input type="password" class="form-control" id="newpassword1" name="newpassword1" />
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label" for="newpassword2">Retype New Password:</label>
        <div class="col-sm-10">
            <input type="password" class="form-control" id="newpassword2" name="newpassword2" />
        </div>
    </div>
    <div class="text-center">
        <input type="submit" class="btn btn-wmffl" value="Change Password"/>
    </div>
</form>


<?php include 'base/footer.php'; ?>
