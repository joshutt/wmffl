<?php
require_once 'utils/start.php';

$title = '2021 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 1/8/2022</H5>
<HR size="1">

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">

    <?php
    $amt_owed = array( );

    if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

        <h2 align="center"><a class="btn btn-wmffl" href="http://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
    <?php } ?>

    <table class="report">
        <tr class="titleRow">
            <th>Team</th>
            <th>Previous</th>
            <th>Paid</th>
            <th>Late Fees</th>
            <th>Illegal<br/>Lineup</th>
            <th>Extra<br/>Transactions</th>
            <th>Wins</th>
            <th>Playoffs</th>
            <th>Balance</th>
            <th>2022 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$534.89</td>
            <td class="padded"><span class="debt">($234.89)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6</td>
            <td class="padded">5.5 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$235.61</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$20.63</td>
            <td class="padded">$54.37</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$21.14</td>
            <td class="padded">$53.86</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$54.09</td>
            <td class="padded">$20.91</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">18</td>
            <td class="padded">6 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$0.12</td>
            <td class="padded">$74.88</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$22.56</td>
            <td class="padded">$52.44</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$18.12</td>
            <td class="padded">$56.88</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$9.21</td>
            <td class="padded">$65.79</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">5.5 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$15.61</td>
            <td class="padded">$59.39</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$174.88</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">25</td>
            <td class="padded">11 x $3.02</td>
            <td class="padded">Division Title - $25.40<br/>Playoff Team - $25.40<br/>First Round Win - $101.60</td>
            <td class="padded">$260.50</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$338.23</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3</td>
            <td class="padded">20</td>
            <td class="padded">10 x $3.02</td>
            <td class="padded">Division Title - $25.40<br/>Playoff Team - $25.40<br/>First Round Win - $101.60<br/>Championship - $127.00</td>
            <td class="padded">$549.83</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded"><span class="debt">($4.34)</span></td>
            <td class="padded">$79.34</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">8</td>
            <td class="padded">6 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$10.12</td>
            <td class="padded">$64.88</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$169.74</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">16</td>
            <td class="padded">5 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$93.84</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$143.84</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">17</td>
            <td class="padded">8 x $3.02</td>
            <td class="padded">Playoff Team - $25.40</td>
            <td class="padded">$101.40</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$359.51</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">6 x $3.02</td>
            <td class="padded">-</td>
            <td class="padded">$300.63</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$177.16</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">8 x $3.02</td>
            <td class="padded">Division Title - $25.40<br/>Division Title - $25.40</td>
            <td class="padded">$177.12</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2020Season/teammoney">2020  results</a></p>

</div>

<?php include 'base/footer.php'; ?>
