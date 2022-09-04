<?php
require_once 'utils/start.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$thequery = "select teamid, password, name, userid from user where username='$username' and (password=password('$password') or password=md5('$password')) and Active='Y'";

$result = mysqli_query($conn, $thequery);
$numrow = mysqli_num_rows($result);
error_log("Num Row: $numrow");
error_log("Username $username");
error_log("Password $password");

if ($numrow == 0) {
    $_SESSION['message'] = 'Invalid Username/Password';
    $_SESSION['isin'] = False;
    $expire = time() + 300;
//    header('Set-Cookie: showlogin=1; expires=$expires; path=/');
    setcookie('showlogin', '1', $expire);
//    header('Location: ' . $_SERVER['HTTP_REFERER']);
//    exit();
    echo "err";
} else {
    $team = mysqli_fetch_row($result);
    $_SESSION['isin'] = True;
    $_SESSION['teamnum'] = $team[0];
    $_SESSION['user'] = $username;
    $_SESSION['usernum'] = $team[3];
    $_SESSION['message'] = '';
    $_SESSION['fullname'] = $team[2];

    $thequery = "update user set lastlog=now(), password=md5('$password') where username='$username'";
    #$thequery = "update user set lastlog=now() where username='$username'";
    $result = mysqli_query($conn, $thequery);
    setcookie('showlogin', '0', time()-3600);
//    header('Location: ' . $_SERVER['HTTP_REFERER']);
//    exit();
    echo "Ok";
}
