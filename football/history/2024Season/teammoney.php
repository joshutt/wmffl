<?php
/**
 * @var $isin boolean
 * @var $teamnum int
 */
require_once 'utils/start.php';

function format($value, $type='currency'): false|string
{
    static $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    if ($value === 0) {
        return '-';
    } else if ($type === 'int') {
        return $value;
    } else if ($value < 0) {
        return '<span class="debt">(' . $formatter->formatCurrency(-$value, 'USD') . ')</span>';
    } else {
        return $formatter->formatCurrency($value, 'USD');
    }
}

$previous = array(1=>182.57, 2=>170.27, 3=>469.00, 4=>3.75, 5=>11.65, 6=>6.60, 7=>526.14, 8=>2.85, 9=>11.80, 10=>415.75, 12=>69.27, 13=>265.38);
$paid = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>63.35, 6=>0, 7=>0, 8=>0, 9=>63.20, 10=>0, 12=>11.75, 13=>0);
$entry = array(1=>75, 2=>75, 3=>75, 4=>0, 5=>75, 6=>75, 7=>75, 8=>0, 9=>75, 10=>75, 12=>75, 13=>75);
$late = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>16, 7=>0, 8=>0, 9=>0, 10=>0, 12=>6, 13=>0);
$illegal = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 12=>0, 13=>0);
$bye = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>4, 9=>0, 10=>0, 12=>0, 13=>0);
$extra = array(1=>0, 2=>0, 3=>7, 4=>0, 5=>11, 6=>0, 7=>21, 8=>0, 9=>10, 10=>0, 12=>0, 13=>0);
$wins = array(1=>4, 2=>3, 3=>5, 4=>6, 5=>5, 6=>6, 7=>4, 8=>2, 9=>5, 10=>6, 12=>7, 13=>7);
$teams = array(1=>'British Bulldogs', 2=> 'Amish Electricians', 3=>'Norsemen', 4=>'Aint Nothing But a Jew Thing', 5=>'Sacks On the Beach',
    6=>'Crusaders', 7=>'MeggaMen', 8=>'Sigourney\'s Beaver', 9=>'Gallic Warriors', 10=>'Trump Molests Collies', 12=>'Richard\'s Lionhearts',
    13=>'Testudos Revenge');
$unpaid = array(6);
$perWin = 2.90;


$title = '2024 WMFFL Financial Statements';
$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 11/16/2024</H5>

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">
    <?php
    $amt_owed = array( 6 => 73.00 );

    if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

        <h2 align="center"><a class="btn btn-wmffl" href="https://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
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
        <tr>
            <?php
            // Sort teams alphabetically
            uksort($teams, function($a, $b) use ($teams) {
                return strcmp($teams[$a], $teams[$b]);
            });

            foreach ($teams as $key=>$value) {
                $balance = $previous[$key] - $entry[$key] + $paid[$key] - $late[$key] - $illegal[$key]*5 - $bye[$key] - $extra[$key] + $wins[$key]*$perWin;
                if (in_array($key, $unpaid)) {
                    $deliquent = true;
                }  else {
                    $deliquent = false;
                }
                ?>
        <tr class="<?= $deliquent ? 'table-danger' : '' ?>">
            <td class="name padded"><?= $value ?></td>
            <td class="padded"><?= format($previous[$key]) ?></td>
            <td class="padded"><?= format($paid[$key]) ?></td>
            <td class="padded"><?= format($late[$key]) ?></td>
            <td class="padded"><?= format($illegal[$key], 'int') ?></td>
            <td class="padded"><?= format($bye[$key], 'int') ?></td>
            <td class="padded"><?= format($extra[$key], 'int') ?></td>
            <td class="padded"><?= format($wins[$key], 'int') ?> x <?= format($perWin) ?></td>
            <td class="padded">-</td>
            <td class="padded"><?= format($balance) ?></td>
            <td class="padded"><?= $deliquent ? format(-$balance) : format(0) ?></td>
        </tr>

            <?php } ?>
    </table>
    <p>Previous column is based on <a href="/history/2023Season/teammoney">2023  results</a></p>

</div>

<?php include 'base/footer.php'; ?>
