<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (!$isin) {
    header("Location: blogentry.php");
    exit;
}

$subject = stripslashes($HTTP_POST_VARS["subject"]);
$body = stripslashes($HTTP_POST_VARS["body"]);

#$sql = "SELECT blogaddress FROM user WHERE userid=$teamnum";
$sql = "SELECT blogaddress FROM user WHERE username='$user'";
//print $sql;
//print "<br/>";
$results = mysql_query($sql) or die("Error in SQL: ".mysql_error());
list($address) = mysql_fetch_row($results);
//print $address;

mail($address, $subject, $body);

header("Location: /comments.shtml");
exit();

?>
