<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$sql = "SELECT MAX(messageId) FROM chat ";
$results = mysql_query($sql) or die("Die: "+mysql_error());
list($message) = mysql_fetch_row($results);

print $message;
?>
