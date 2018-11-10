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
    <div class="row <?= $margin ?> justify-content-between">
        <div class="boxScore col-8 pl-1 pr-0 text-left text-truncate"><?= $t->name ?></div>
        <div class="boxScore col-4 px-0 d-block text-center"><?= $t->printShortRecord() ?></div>
    </div>
<?php } ?>
</div>




