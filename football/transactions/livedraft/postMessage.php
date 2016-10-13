<?
require_once "utils/start.php";

$message = $_REQUEST["message"];
//$last = $_REQUEST["last"];
$league = $_REQUEST["league"];
/*
print $message;
print "<br/>";
print mysql_escape_string($message);
print "<br/>";
*/
#print $_SESSION["userid"];
$toUseId = $_SESSION["usernum"];
//$toUseId = $_SESSION["userid"];

if ($league == "true") {
    $toUseId=0;
}

$sql = "INSERT INTO chat (userid, message) VALUES ($toUseId, '".mysql_escape_string($message)."')";
//print $sql;
mysql_query($sql) or die("Die: "+mysql_error());


include "chat.php";
?>
