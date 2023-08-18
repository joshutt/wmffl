<?php
require_once 'utils/start.php';

$title = '2023 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 8/18/2023</H5>
<HR size="1">

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">

    <?php
    $amt_owed = array( 1 => 53.23, 6 => 167.44, 9 => 65.34, 12 => 82.56, 5 => 46.50 );

    if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

        <h2 align="center"><a class="btn btn-wmffl" href="https://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
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
            <th>2023 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$170.27</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$95.27</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$21.14</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$21.77</td>
            <td class="padded">$53.23</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded"><span class="debt">($92.44)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($92.44)</span></td>
            <td class="padded">$167.44</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">*</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$9.66</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$9.66</td>
            <td class="padded">$65.34</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$344.64</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$269.64</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$533.35</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$458.35</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded"><span class="debt">($7.56)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($7.56)</span></td>
            <td class="padded">$82.56</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$28.50</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$28.50</td>
            <td class="padded">$46.50</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">*</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$273.72</td>
            <td class="padded">$45.06 #</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$243.78</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$412.65</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$337.65</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2022Season/teammoney">2022  results</a></p>
    <p>* - The Fighting Squirrels and Sean Taylor's Ashes do not currently have owners</p>
    <p># - When Don left he transfered the $45.06 from his account to Testudos Revenge</p>

</div>

<?php include 'base/footer.php'; ?>
