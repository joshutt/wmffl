<?php
require_once "utils/start.php";

// Make sure post variable exists
if (!isset($_POST)) {
    print json_encode(array("code" => -1, "msg" => "ERROR: Please provide username and password"));
    exit();
}

//Make sure username and password have been passed
$userName = $_POST["user"];
$password = $_POST["pass"];
if (!isset($userName) || !isset($password)) {
    print json_encode(array("code"=>-1, "msg"=>"ERROR: Please provide username and password"));
    exit();
}


$query = "SELECT teamid, name, userid, commish FROM user WHERE username='".mysql_real_escape_string($userName)."' AND password=md5('".mysql_real_escape_string($password)."') AND active='Y'";
$result = mysql_query($query);
$numRows = mysql_num_rows($result);
$count = mysql_fetch_assoc($result);

if ($numRows != 1) {
    print json_encode(array("code"=>-1, "msg"=>"ERROR: Invalid username/password combination"));
    exit();
}

$_SESSION["isin"] = true;
$_SESSION["teamnum"] = $count["teamid"];
$_SESSION["usernum"] = $count["userid"];
$_SESSION["fullname"] = $count["name"];


$sql = "SELECT CONCAT(p.lastname, ', ', p.firstname, ' - ', p.pos, ' - ', r.nflteamid)
FROM draftPickHold d JOIN newplayers p ON d.playerid=p.playerid
JOIN nflrosters r on r.playerid=d.playerid and r.dateoff is null
WHERE d.teamid={$count["teamid"]}";
$result2 = mysql_query($sql);
$playerArray = mysql_fetch_array($result2);

//print json_encode($count);
print json_encode(array("code"=>1, "results"=>$_SESSION, "pre"=>$playerArray));

?>
