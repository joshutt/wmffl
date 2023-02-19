<?php
require_once 'utils/start.php';

$title = '2022 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 2/19/2023</H5>
<HR size="1">

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">

    <?php
    $amt_owed = array( 1 => 53.23, 6 => 167.44, 8 => 56.34, 9 => 65.34, 12 => 82.56, 5 => 46.50, 4 => 29.94 );

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
            <th>2023 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$235.61</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">9</td>
            <td class="padded">6 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded">$170.27</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$21.14</td>
            <td class="padded">$53.86</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded">$21.77</td>
            <td class="padded">$53.23</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$0.12</td>
            <td class="padded">-</td>
            <td class="padded">$26.00</td>
            <td class="padded">4</td>
            <td class="padded">-</td>
            <td class="padded">4 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($92.44)</span></td>
            <td class="padded">$167.44</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$18.12</td>
            <td class="padded">$56.88</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded">$18.66</td>
            <td class="padded">$56.34</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$15.61</td>
            <td class="padded">$59.39</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">9</td>
            <td class="padded">6 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded">$9.66</td>
            <td class="padded">$65.34</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$260.50</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">27</td>
            <td class="padded">9.5 x $3.11</td>
            <td class="padded">Division Title - $26.10<br/>Playoff Team - $26.10<br/>First Round Win - $104.40</td>
            <td class="padded">$344.64</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$549.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">29</td>
            <td class="padded">12 x $3.11</td>
            <td class="padded">Division Title - $26.10<br/>Playoff Team - $26.10</td>
            <td class="padded">$533.35</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$10.12</td>
            <td class="padded">$70.88</td>
            <td class="padded">$6.00</td>
            <td class="padded">-</td>
            <td class="padded">20</td>
            <td class="padded">4 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($7.56)</span></td>
            <td class="padded">$82.56</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$93.84</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">9</td>
            <td class="padded">6 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded">$28.50</td>
            <td class="padded">$46.50</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$101.40</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6 x $3.11</td>
            <td class="padded">-</td>
            <td class="padded">$45.06</td>
            <td class="padded">$29.94</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$300.63</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6</td>
            <td class="padded">9 x $3.11</td>
            <td class="padded">Playoff Team - $26.10</td>
            <td class="padded">$273.72</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$177.12</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3</td>
            <td class="padded">-</td>
            <td class="padded">8.5 x $3.11</td>
            <td class="padded">Division Title - $26.10<br/>Playoff Team - $26.10<br/>First Round Win - $104.40<br/>Championship - $130.50</td>
            <td class="padded">$412.65</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2021Season/teammoney">2021  results</a></p>

</div>

<?php include 'base/footer.html'; ?>
