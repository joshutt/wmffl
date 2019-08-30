<?
require_once "/utils/start.php";

if ($isin) {

    $name = $_POST["Name"];
    $team = $_POST["Team"];
    $email = $_POST["email"];
    $proposal = $_POST["proposal"];

$subject = "RULE PROPOSAL";
$body = "$name ($email) of the $team has made the following proposal:\n$proposal";
mail("proposals@wmffl.com", $subject, $body, "From: webmaster@wmffl.com");

header("Location: ballotthanks.php");

} else {


}
?>
