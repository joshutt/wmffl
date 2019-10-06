<?php
require_once "utils/start.php";

$title = "2019 WMFFL Financial Statements";

$cssList = array("/base/css/money.css");
include "base/menu.php";
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 10/5/2019</H5>
<HR size="1">

<p>
    <? include "base/statbar.html" ?>
</p>


<div class="center">

    <?
    $amt_owed = array( 6=>83.20, 4=>73.55 );

    if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

        <h2 align="center"><a href="http://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
    <? } ?>

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
            <th>2019 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$533.01</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$466.26</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">-</td>
            <td class="padded">$150.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$2.75</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$3.30</td>
            <td class="padded">-</td>
            <td class="padded">$11.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($83.20)</span></td>
            <td class="padded">$83.20</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$5.18</td>
            <td class="padded">$69.82</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$5.50</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$3.83</td>
            <td class="padded">$71.17</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$4.50</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$559.68</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$487.43</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$176.06</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$109.31</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$26.17</td>
            <td class="padded">$48.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$2.75</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$308.62</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$239.12</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$13.95</td>
            <td class="padded">-</td>
            <td class="padded">$11.00</td>
            <td class="padded">1</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($73.55)</span></td>
            <td class="padded">$73.55</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$306.29</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$236.79</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$299.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.75</td>
            <td class="padded">-</td>
            <td class="padded">$233.08</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2018Season/teammoney">2018  results</a></p>

</div>

<? include "base/footer.html"; ?>
