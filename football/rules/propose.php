<?
require_once "/utils/start.php";

if ($isin) {

$name = $HTTP_POST_VARS["Name"];
$team = $HTTP_POST_VARS["Team"];
$email = $HTTP_POST_VARS["email"];
$proposal = $HTTP_POST_VARS["proposal"];

$subject = "RULE PROPOSAL";
$body = "$name ($email) of the $team has made the following proposal:\n$proposal";
mail("proposals@wmffl.com", $subject, $body, "From: webmaster@wmffl.com");

header("Location: ballotthanks.php");

} else {


}
?>
