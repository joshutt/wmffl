<?php
require_once 'utils/connect.php';

if (!isset($_POST['submit'])) {
    header('Location: protections');
    exit;
}

$playerlist = '(';
if (!empty($protect)) {
    foreach ($protect as $value) {
        $playerlist .= "$value, ";
    }
}
$playerlist .= '0) ';


$checkQuery = "select max(pos.cost) as 'cost' ";
$checkQuery .= 'from newplayers p ';
$checkQuery .= 'join roster r on p.playerid=r.playerid and r.dateoff is null ';
$checkQuery .= 'join positioncost pos on pos.position=p.pos ';
$checkQuery .= 'left join protectioncost pc on ';
$checkQuery .= 'p.playerid=pc.playerid ';
$checkQuery .= "and pc.season=$currentSeason ";
$checkQuery .= 'left join protections pro on ';
$checkQuery .= 'pro.playerid=p.playerid and ';
$checkQuery .= "pro.teamid=r.teamid and pro.season=$currentSeason ";
$checkQuery .= "where r.teamid=$teamnum ";
$checkQuery .= 'and (pos.years<=pc.years or pos.years=0) ';
$checkQuery .= "and p.playerid in $playerlist ";
$checkQuery .= 'GROUP BY p.playerid';

$checkCost = 'SELECT protectionpts, totalpts FROM transpoints ';
$checkCost .= "WHERE teamid=$teamnum and season=$currentSeason";

$delQuery = "DELETE FROM protections WHERE season=$currentSeason ";
$delQuery .= "AND teamid=$teamnum";

$insQuery = 'INSERT INTO protections (teamid, playerid, season, cost) ';
$insQuery .= "select r.teamid, p.playerid, $currentSeason, ";
$insQuery .= "max(pos.cost) as 'cost' ";
$insQuery .= 'from newplayers p ';
$insQuery .= 'join roster r on p.playerid=r.playerid and r.dateoff is null ';
$insQuery .= 'join positioncost pos on pos.position=p.pos ';
$insQuery .= 'left join protectioncost pc ';
$insQuery .= 'on p.playerid=pc.playerid ';
$insQuery .= "and pc.season=$currentSeason ";
$insQuery .= "where r.teamid=$teamnum and ";
$insQuery .= '(pos.years<=pc.years or pos.years=0) ';
$insQuery .= "and p.playerid in $playerlist ";
$insQuery .= 'GROUP BY p.playerid';

$detCost = 'SELECT sum(cost) FROM protections ';
$detCost .= "WHERE teamid=$teamnum AND Season=$currentSeason";

$updCost = 'UPDATE transpoints SET protectionpts=';

$display = "select CONCAT(p.firstname, ' ', p.lastname), ";
$display .= 'p.pos, p.team, pro.cost ';
$display .= 'from newplayers p, protections pro ';
$display .= 'where p.playerid=pro.playerid ';
$display .= "and pro.season=$currentSeason and pro.teamid=$teamnum ";
$display .= 'order by p.pos, p.lastname ';

$title = 'WMFFL Protections';

include 'base/menu.php';
?>

<h1>Protections</h1>
<HR size="1">

<div class="container">
    <?php
    if ($isin) {

        // Gather costs
        $result = mysqli_query($conn, $checkQuery);
        $totalCost = 0;
        while (list($thiscost) = mysqli_fetch_row($result)) {
            $totalCost += $thiscost;
        }

        $result = mysqli_query($conn, $checkCost);
        $thiscost = mysqli_fetch_row($result);
        if ($totalCost > $thiscost[1]) {
            ?>
            <div class="alert alert-secondary" role="alert">
                You spent too many protection points<br/>
                Spent: <?= $totalCost ?><br/>
                Allowed: <?= $thiscost[0] ?>
            </div>
            <?php
        } else {

            // Remove old and insert new protections
            mysqli_query($conn, $delQuery) or die('Delete Query');
            mysqli_query($conn, $insQuery) or die('Insert Query: ' . mysqli_error($conn));

            // Determine the new cost
            $updCost .= $totalCost;
            $updCost .= " WHERE teamid=$teamnum and season=$currentSeason";
            mysqli_query($conn, $updCost) or die('Update Cost Query');
            ?>
            <div class="alert alert-secondary alert-dismissible" role="alert">
                Your protections have been saved.
                You may revise them anytime until the deadline.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="row justify-content-center">
                <div class="col-3"><strong>Player</strong></div>
                <div class="col-1"><strong>Cost</strong></div>
            </div>

            <?php
            $results = mysqli_query($conn, $display);
            while (list($player, $pos, $team, $cost) = mysqli_fetch_row($results)) {
                ?>
                <div class="row justify-content-center">
                    <div class="col-3"><?= $player ?> (<?= $pos ?>-<?= $team ?>)</div>
                    <div class="col-1"><?= $cost ?></div>
                </div>
                <?php
            }
            ?>
            <div class="row justify-content-center">
                <div class="col-3"><strong>TOTAL</strong></div>
                <div class="col-1"><strong><?= $totalCost ?></strong></div>
            </div>
            <?php
        }
        ?>
        <div class="row justify-content-center">
            <button class="btn btn-wmffl"><a href="protections">Change Protections</a></button>
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-secondary" role="alert">
            You must be logged in to save protections.
        </div>
        <?php
    }
    ?>
</div>
<?php
include 'base/footer.php';
?>

