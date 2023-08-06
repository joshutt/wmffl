<?
require_once "utils/start.php";

$thisWeek = $_REQUEST["week"];
if ($thisWeek == "") {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason=2009;
$title = "Standings";

$clinchedList = array();

?>

<? include "base/menu.php"; ?>

<style>
<!--
H4 {color:660000; text-decoration:None; font-size:14pt; font-weight:bold}

.othertitle {background:#660000 ; color:#e2a500; text-decoration:bold; font-size:14pt; text-align=center; font-family: times, courier; width: 100%;}
-->
</style>

<table width="100%">
<tr><td class="othertitle" align="center">Current Standings</td></tr></table>
<center>
<? include "weekstandings.php"; ?>

<p>
x - clinched playoff berth<br/>
y - clinched division title<br/>
z - clinched Toilet Bowl berth
</p>
</center>


<? include "base/footer.php"; ?>
