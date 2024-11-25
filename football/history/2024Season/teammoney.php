<?php
/**
 * @var $isin boolean
 * @var $teamnum int
 * @var $entityManager EntityManager
 * @var int $currentSeason
 * @var $conn mysqli
 */

use WMFFL\orm\Paid;

require_once 'utils/start.php';
require_once 'bootstrap.php';

require_once '../common/moneyUtil.php';

// Get Paid Objects for this season, sorted by team name
$qb = $entityManager->createQueryBuilder();
$query = $qb->select('p')
    ->from('WMFFL\orm\Paid', 'p')
    ->join('p.team', 't')
    ->where('p.season = :season')
    ->orderBy('t.name', 'ASC')
    ->setParameter('season', $currentSeason)
    ->getQuery();
$paidArr = $query->getResult();

$rows = getExtraCharges($entityManager, $currentSeason);
$wins = getWins($entityManager, $currentSeason);
//print '<pre>';
//print_r($wins);
//print '</pre>';

// Magic Numbers
$illegalActivations = 5;
$byeWeekActivations = 1;
$extraTransactions = 1;
$numOfGames = 84;
$entryFee = 75;
$winPercent = 0.25;
$postPercent = 0.5;
$divPercent = 0.05;
$playoffPercent = 0.05;
$finalPercent = 0.25;
$champPercent = 0.50;


// Get Data for each team
$teamRow = array();
$fullNeg = 0;
/** @var $p Paid */
foreach ($paidArr as $p) {
    $id = $p->getTeam()->getId();
    $fines = $rows[$id];
    $overage = $fines['Remaining'] < 0 ? -$fines['Remaining']*$extraTransactions : 0;

    $teamRow[$id]['name'] = $fines['name'];
    $teamRow[$id]['deliquent'] = !$p->isPaid();
    $teamRow[$id]['fines'] = $rows[$id];
    $teamRow[$id]['overage'] = $overage;
    $neg = $p->getLateFee() + $fines['illegal'] * $illegalActivations + $fines['byeWeek'] * $byeWeekActivations + $overage;
    $fullNeg += $neg;
    $teamRow[$id]['negative'] = $neg;
    $teamRow[$id]['previous'] = $p->getPrevious();
    $teamRow[$id]['entry'] = $p->getEntry();
    $teamRow[$id]['paid'] = $p->getAmtPaid();
    $teamRow[$id]['lateFee'] = $p->getLateFee();
    $teamRow[$id]['illegal'] = $fines['illegal'];
    $teamRow[$id]['byeWeek'] = $fines['byeWeek'];
    $teamRow[$id]['wins'] = $wins[$id]['wins'] + $wins[$id]['ties'] / 2;
    $teamRow[$id]['playoffs'] = array();
}

//// Temp for testing
//$pt = array_rand($teamRow, 4);
//foreach($pt as $p) {
//    $teamRow[$p]['playoffs'][] = 'p';
//}
//
//$div = array_rand(array_flip($pt), 3);
//foreach($div as $d) {
//    $teamRow[$d]['playoffs'][] = 'd';
//}
//
//$finals = array_rand(array_flip($pt), 2);
//foreach($finals as $f) {
//    $teamRow[$f]['playoffs'][] = 'f';
//}
//
//$champ = array_rand(array_flip($finals), 1);
//    $teamRow[$champ]['playoffs'][] = 'c';


// Calulated values for wins
$totalPot = $entryFee * sizeof($teamRow) + $fullNeg;
$perWin = round($totalPot * $winPercent / $numOfGames, 2);
$playoffPot = $totalPot * $postPercent;
$divsionWin = round($playoffPot * $divPercent, 2);
$playoffApp = round($playoffPot * $playoffPercent, 2);
$champApp = round($playoffPot * ($finalPercent - $playoffApp), 2);
$champWin = round($playoffPot * ($champPercent - $finalPercent), 2);

// Calculate each team's balance
$amt_owed = array();
foreach ($teamRow as $id => &$t) {
    $winnings = 0;
    $playoffStr = '';
    if (in_array('d', $t['playoffs'])) {
        $winnings += $divsionWin;
        $playoffStr .= 'Division Title - ' . format($divsionWin) . '<br/>';
    }

    if (in_array('p', $t['playoffs'])) {
        $winnings += $playoffApp;
        $playoffStr .= 'Playoff Team - ' . format($playoffApp) . '<br/>';
    }

    if (in_array('f', $t['playoffs'])) {
        $winnings += $champApp;
        $playoffStr .= 'First Round Win - ' . format($champApp) . '<br/>';
    }

    if (in_array('c', $t['playoffs'])) {
        $winnings += $champWin;
        $playoffStr .= 'Championship - ' . format($champWin);
    }

    $t['balance'] = $t['previous'] - $t['entry'] + $t['paid'] - $t['negative'] + $t['wins'] * $perWin + $winnings;
    if ($t['balance'] >= 0) {
        $t['deliquent'] = false;
    }
    $t['stillOwe'] = $t['deliquent'] ? -$t['balance'] : 0;
    $t['playoffStr'] = empty($playoffStr) ? '-' : $playoffStr;
    if ($t['stillOwe'] > 0) {
        $amt_owed[$id] = $t['stillOwe'];
    }
};


$title = '2024 WMFFL Financial Statements';
$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<h5 align="center">Updated <?= date('m/d/Y') ?></h5>

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">
    <?php
    if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>
        <h2 align="center"><a class="btn btn-wmffl" href="https://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay
                Now</a></h2>
    <?php } ?>

    <table class="report table table-striped table-hover">
        <tr class="titleRow">
            <th>Team</th>
            <th>Previous</th>
            <th>Paid</th>
            <th>Late Fees</th>
            <th>Illegal<br/>Lineup</th>
            <th>Bye Week<br/>Activation</th>
            <th>Extra<br/>Transactions</th>
            <th>Wins</th>
            <th>Playoffs</th>
            <th>Balance</th>
            <th>2024 Fee</th>
        </tr>
        <?php
        // Print each row
        foreach ($teamRow as $id => $team) {
            ?>
            <tr class="<?= $team['deliquent'] ? 'table-danger' : '' ?>">
                <td class="name padded"><?= $team['name'] ?></td>
                <td class="padded"><?= format($team['previous']) ?></td>
                <td class="padded"><?= format($team['paid']) ?></td>
                <td class="padded"><?= format($team['lateFee']) ?></td>
                <td class="padded"><?= format($team['illegal'], 'int') ?></td>
                <td class="padded"><?= format($team['byeWeek'], 'int') ?></td>
                <td class="padded"><?= format($team['overage'], 'int') ?></td>
                <td class="padded"><?= format($team['wins'], 'int') ?> x <?= format($perWin) ?></td>
                <td class="padded"><?= $team['playoffStr'] ?></td>
                <td class="padded"><?= format($team['balance']) ?></td>
                <td class="padded"><?= format($team['stillOwe']) ?></td>
            </tr>
        <?php } ?>
    </table>
    <p>Previous column is based on <a href="/history/2023Season/teammoney">2023 results</a></p>

</div>

<?php include 'base/footer.php'; ?>
