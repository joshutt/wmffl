<?
require_once "utils/connect.php";

//include "lib/Team.php";


$thisSeason = $currentSeason;
$thisWeek = $currentWeek;
if ($thisWeek == 0) {
    $thisWeek = 16;
    $thisSeason = $thisSeason - 1;
}
$display = 0;
include "history/common/weekstandings.php";
?>

<div class="cat text-center">STANDINGS</div>
<div class="container gameBox pb-1">
<?php
$thisDiv = "";
foreach ($teamArray as $t) {
    $margin = "mt-0";
    if ($t->division != $thisDiv) {
        $thisDiv = $t->division;
        $margin = "mt-2";
        ?>
    <?php } ?>
    <div class="row <?= $margin ?>">
        <div class="boxScore col-6 pl-1 text-left"><?= $t->name ?></div>
        <div class="boxScore col-6  text-right"><?= $t->getPrintRecord() ?></div>
    </div>
<?php } ?>
</div>




