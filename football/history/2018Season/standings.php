<?php
require_once "utils/start.php";

$thisWeek = $_REQUEST["week"];
if ($thisWeek == "") {
    $thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason=2018;
$title = "Standings";

$clinchedList = array('Trump Molests Collies' => 'y-', 'Amish Electricians' => 'e-', 'Fighting Squirrels' => 'e-');

include "base/menu.php";
?>


<table width="100%">
<tr><td class="cat" align="center">Current Standings</td></tr></table>
<center>
<? include "../common/weekstandings.php"; ?>

<?
if (!empty($clinchedList)) {
?>

<p class="my-4 text-center">
e - eliminated from playoffs<br/>
x - clinched playoff berth<br/>
y - clinched division title<br/>
z - clinched Toilet Bowl berth
</p>
</center>
<?php } ?>

<? include "base/footer.html"; ?>
