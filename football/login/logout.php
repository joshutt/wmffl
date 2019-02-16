<?
require_once "utils/start.php";

unset($_SESSION["teamnum"]);
unset($_SESSION["user"]);
unset($_SESSION["fullname"]);
$_SESSION["isin"] = False;

header("Location: ".$HTTP_REFERER);
?>
