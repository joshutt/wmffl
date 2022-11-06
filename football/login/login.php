<?php
require_once 'utils/start.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$thequery = "select teamid, password, name, userid from user where username=? and password=md5(?) and Active='Y'";
$stmt = mysqli_prepare($conn, $thequery);

// Bind the parameters
mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$numrow = mysqli_num_rows($result);
error_log("Num Row: $numrow");
error_log("Username $username");
error_log("Password $password");

if ($numrow == 0) {
    $errorMsg = 'Invalid Username/Password';
    $_SESSION['message'] = $errorMsg;
    $_SESSION['isin'] = False;
    $expire = time() + 300;
//    header('Set-Cookie: showlogin=1; expires=$expires; path=/');
    setcookie('showlogin', '1', $expire);
//    header('Location: ' . $_SERVER['HTTP_REFERER']);
//    exit();
    echo "err - $errorMsg";
} else {
    $team = mysqli_fetch_row($result);
    $_SESSION['isin'] = True;
    $_SESSION['teamnum'] = $team[0];
    $_SESSION['user'] = $username;
    $_SESSION['usernum'] = $team[3];
    $_SESSION['message'] = '';
    $_SESSION['fullname'] = $team[2];

    //$thequery = "update user set lastlog=now(), password=md5('$password') where username='$username'";
    $thequery = 'update user set lastlog=now() where username=?';
    $stmt = mysqli_prepare($conn, $thequery);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);

    setcookie('showlogin', '0', time()-3600);
//    header('Location: ' . $_SERVER['HTTP_REFERER']);
//    exit();
    echo 'Ok';
}
