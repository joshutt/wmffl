<?
require_once "utils/start.php";

$thequery = "select teamid, password, name, userid from user where username='".$username."' and (password=password('".$password."') or password=md5('$password')) and Active='Y'";
$result = mysql_query($thequery, $conn);
$numrow = mysql_num_rows($result);

if ($username == "commish" && $password == "secret") {
    print "You are the commish";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

if ($numrow == 0) {
    $_SESSION["message"] = "Invalid Username/Password";
    $_SESSION["isin"] = False;
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
else {
    $team = mysql_fetch_row($result);		
    $_SESSION["isin"] = True;
    $_SESSION["teamnum"] = $team[0];
    $_SESSION["user"] = $username;
    $_SESSION["usernum"] = $team[3];
    $_SESSION["message"] = "";
    $_SESSION["fullname"] = $team[2];

    $thequery = "update user set lastlog=now(), password=md5('$password') where username='$username'";
    #$thequery = "update user set lastlog=now() where username='$username'";
    $result = mysql_query($thequery, $conn);
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
