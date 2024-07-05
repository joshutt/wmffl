<?php
/**
 * @var $currentSeason int
 * @var $currentWeek int
 */
require_once 'utils/connect.php';

$thisSeason = $currentSeason;
$thisWeek = $currentWeek;
if ($thisWeek == 0) {
    $thisWeek = 16;
    $thisSeason = $thisSeason - 1;
}
$display = 0;
include 'history/common/weekstandings.php';
?>

<div class="cat text-center">STANDINGS</div>
<div class="container gameBox pb-1">
<?php
$thisDiv = '';
foreach ($teamArray as $t) {
    $margin = 'mt-0';
    if ($t->division != $thisDiv) {
        if ($thisDiv !== '') {
            ?>
</div>
            <?php
            $margin = 'mt-3';
        }
        $thisDiv = $t->division;
        ?>
        <div class="border-bottom border-top p-1">
            <?php
        ?>
    <?php } ?>
    <div class="mt-0 justify-content-between">
        <a class="row m-0" href="/teams/teamschedule?viewteam=<?= $t->teamid ?>">
        <div class="boxScore col-8 pl-1 pr-0 text-left text-truncate"><?= $t->name ?></div>
        <div class="boxScore col-4 px-0 d-block text-center"><?= $t->printShortRecord() ?></div>
        </a>
    </div>
<?php } ?>
</div>




