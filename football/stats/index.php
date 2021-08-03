<?php
$title = "Stats Index";
//$cssList = array("/base/css/index.css");
include "base/menu.php"; 
?>
<span class="title"><?= $title ?></span>

<div class="btn-group" role="group">
    <a class="btn btn-wmffl" href="leaders">League Leaders</a>
    <a class="btn btn-wmffl" href="weekbyweek">Week By Week</a>
    <a class="btn btn-wmffl" href="powerrate">Power Rank</a>
    <a class="btn btn-wmffl" href="playerstats">Player Stats</a>
    <a class="btn btn-wmffl" href="luck">Luck Ratings</a>
    <a class="btn btn-wmffl" href="playerrecord">Player Records</a>
    <a class="btn btn-wmffl" href="/history/2018Season/teammoney">Accounting</a>
</div>

<?php include "base/footer.php"; ?>
