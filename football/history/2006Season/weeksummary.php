<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$thisWeek = $_REQUEST["week"];
$thisSeason = 2006;
$title = "Week $thisWeek Recap";
?>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<style>
<!--
H4 {color:660000; text-decoration:None; font-size:14pt; font-weight:bold}

-->
</style>


<h1 align="center"><? print $title; ?></h1>
<hr/>

<p>
<? include "summary$thisWeek.inc"; ?>
</p>

<p>
<h4 class="headline">Weekly Scores</h4>
<? include "weeklyscores.php"; ?>
</p>


<p>
<h4>Current Standings</h4>
<? include "weekstandings.php"; ?>
</p>

<p>
<h4>Next Week's Games</h4>
<? include "nextgame.php"; ?>
</p>

<p>
<h4>Box Scores</h4>
<? include "boxscores.php"; ?>
</p>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
