<?
session_start();
require_once "base/conn.php";
require "login/loginglob.php";
require_once "loadTrades.inc.php";
if (!$isin) {
    header("Location: tradescreen.php");
    exit;
}

$offerid = $_GET["offerid"];
rejectTrade($conn, $offerid);
?>

<html>
<head>
<title>Invalid Trade</title>
</head>

<? include "base/menu.php"; ?>

<h1 align="center">Trade Screen</h1>
<hr/>

<p>Your trade was invalid and has been cancelled.  This most likely occurred for one of the following reasons reasons:</p>
<ul>
<li>A player being traded is no longer on the roster of the team trading him OR</li>
<li>Draft picks being traded no longer belong to the team trading them OR</li>
<li>A team trading protection/transaction points don't have a sufficent number of points remaining</li>
</ul>


<p>Return to <a href="tradescreen.php">trade screen</a>.</p>

</body>
</html>
