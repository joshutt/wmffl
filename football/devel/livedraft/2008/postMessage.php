<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$message = $_REQUEST["message"];
/*
print $message;
print "<br/>";
print mysql_escape_string($message);
print "<br/>";
*/
#print $_SESSION["userid"];
$userid = $_SESSION["userid"];

$sql = "INSERT INTO chat (userid, message) VALUES ($userid, '".mysql_escape_string($message)."')";
//print $sql;
mysql_query($sql) or die("Die: "+mysql_error());


include "chat.php";
?>
