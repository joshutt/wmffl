<?php
require_once 'utils/start.php';
$title = 'WMFFL Activations';
$week = $currentWeek;
$season = $currentSeason;

include 'base/menu.php';
?>

<h1 class="full">Activations</h1>

<?php
include 'activationButtons.php';
?>

<CENTER>
    <?php include 'currentactivations.php'; ?>
</CENTER>

    <?php include 'base/footer.php'; ?>

