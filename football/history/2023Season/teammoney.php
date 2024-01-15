<?php
require_once 'utils/start.php';

$title = '2023 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 1/15/2024</H5>
<HR size="1">

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">

    <?php
    $amt_owed = array( 6 => 68.40, 9=>63.20, 12=>5.75, 5=>63.35);

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
            <th>2024 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Aint Nothing But a Jew Thing</td>
            <td class="padded">$0.00</td>
            <td class="padded">$150.00</td>
            <td class="padded">-</td>
            <td class="padded">11</td>
            <td class="padded">-</td>
            <td class="padded">5 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$3.75</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$170.27</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">13</td>
            <td class="padded">3 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$91.12</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$21.14</td>
            <td class="padded">$53.23</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">-</td>
            <td class="padded">12 x $2.95</td>
            <td class="padded">Division Title - $24.80<br/>Playoff Team - $24.80<br/>First Round Win - $99.20</td>
            <td class="padded">$182.57</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Crusaders</td>
            <td class="padded"><span class="debt">($92.44)</span></td>
            <td class="padded">$167.44</td>
            <td class="padded">-</td>
            <td class="padded">10</td>
            <td class="padded">7</td>
            <td class="padded">8 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$6.60</td>
            <td class="padded">$68.40</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$9.66</td>
            <td class="padded">$65.34</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$11.80</td>
            <td class="padded">$63.20</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$344.64</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">21</td>
            <td class="padded">10 x $2.95</td>
            <td class="padded">Playoff Team - $24.80<br/>First Round Win - $99.20<br/>Championship - $124.00</td>
            <td class="padded">$526.14</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$533.35</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">6</td>
            <td class="padded">7 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$469.00</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded"><span class="debt">($7.56)</span></td>
            <td class="padded">$82.56</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">7 x $2.95</td>
            <td class="padded">Division Title - $24.80<br/>Playoff Team - $24.80</td>
            <td class="padded">$69.25</td>
            <td class="padded">$5.75</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$28.50</td>
            <td class="padded">$46.50</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">9</td>
            <td class="padded">7 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$11.65</td>
            <td class="padded">$63.35</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sigourney's Weaver's</td>
            <td class="padded">$0.00</td>
            <td class="padded">$150.00</td>
            <td class="padded">-</td>
            <td class="padded">6</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$2.85</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$273.72</td>
            <td class="padded">$45.06 #</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">-</td>
            <td class="padded">8 x $2.95</td>
            <td class="padded">-</td>
            <td class="padded">$265.38</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$412.65</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">-</td>
            <td class="padded">10 x $2.95</td>
            <td class="padded">Division Title - $24.80<br/>Playoff Team - $24.80</td>
            <td class="padded">$415.75</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2022Season/teammoney">2022  results</a></p>
    <p># - When Don left he transfered the $45.06 from his account to Testudos Revenge</p>

</div>

<?php include 'base/footer.php'; ?>
