<?
require_once "utils/start.php";

if (!$isin) {
    header("Location: blogentry.php");
    exit;
}

require "DataObjects/Forum.php";

$post = new DataObjects_Forum;


$subject = stripslashes(mysql_real_escape_string($_POST["subject"]));
$body = stripslashes(mysql_real_escape_string(str_replace("\r\n", "", $_POST["body"])));


$post->settitle($subject);
$post->setbody($body);
$post->setuserid($usernum);
$post->setcreateTime(date("Y-n-d G:i:s"));
$id = $post->insert();
#print $id;

#print_r($post);

/*
#$sql = "SELECT blogaddress FROM user WHERE userid=$teamnum";
$sql = "SELECT blogaddress FROM user WHERE username='$user'";
//print $sql;
//print "<br/>";
$results = mysql_query($sql) or die("Error in SQL: ".mysql_error());
list($address) = mysql_fetch_row($results);
//print $address;

mail($address, $subject, $body);
*/

header("Location: comments.php");
exit;
?>
