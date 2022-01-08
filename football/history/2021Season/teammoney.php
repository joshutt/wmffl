<?php
require_once 'utils/start.php';

$title = '2021 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 11/13/2021</H5>
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
            <th>2021 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$534.89</td>
            <td class="padded"><span class="debt">($234.89)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">2.5 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$230.08</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$20.63</td>
            <td class="padded">$54.37</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$14.15</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$54.09</td>
            <td class="padded">$20.91</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">13</td>
            <td class="padded">4 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($1.68)</span></td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$22.56</td>
            <td class="padded">$52.44</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$11.32</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$9.21</td>
            <td class="padded">$65.79</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4.5 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$12.74</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$174.88</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">16</td>
            <td class="padded">7 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$103.69</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$338.23</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3</td>
            <td class="padded">-</td>
            <td class="padded">7 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$280.04</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded"><span class="debt">($4.34)</span></td>
            <td class="padded">$79.34</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$11.32</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$169.74</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">10</td>
            <td class="padded">3 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$93.23</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$143.84</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">8</td>
            <td class="padded">5 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$74.99</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$359.51</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$293.00</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$177.16</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $2.83</td>
            <td class="padded">-</td>
            <td class="padded">$116.31</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2020Season/teammoney">2020  results</a></p>

</div>

<?php include 'base/footer.html'; ?>
