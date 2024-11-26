<?php
/**
 * @var $isin boolean
 * @var $teamnum int
 */
require_once 'utils/start.php';

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
    $amt_owed = array( 6 => 67.00 );

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
        <tr class="">
            <td class="name padded">Aint Nothing But a Jew Thing</td>
            <td class="padded">$3.75</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$21.15</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$170.27</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$103.97</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$182.57</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$119.17</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="table-danger">
            <td class="name padded">Crusaders</td>
            <td class="padded">$6.60</td>
            <td class="padded">-</td>
            <td class="padded">$16.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($67.00)</span></td>
            <td class="padded">$67.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$11.80</td>
            <td class="padded">$63.20</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">10</td>
            <td class="padded">5 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$4.50</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$526.14</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">21</td>
            <td class="padded">4 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$441.74</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Norsemen</td>
            <td class="padded">$469.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7</td>
            <td class="padded">5 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$401.50</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$69.27</td>
            <td class="padded">$11.75</td>
            <td class="padded">$6.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$20.30</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$11.65</td>
            <td class="padded">$63.35</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">11</td>
            <td class="padded">5 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$3.50</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Sigourney's Beaver</td>
            <td class="padded">$2.85</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$4.65</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$265.38</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$210.68</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$415.75</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6 x $2.90</td>
            <td class="padded">-</td>
            <td class="padded">$358.15</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2023Season/teammoney">2023  results</a></p>

</div>

<?php include 'base/footer.php'; ?>
