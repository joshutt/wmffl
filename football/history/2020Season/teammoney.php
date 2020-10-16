<?php
require_once "utils/start.php";

$title = "2020 WMFFL Financial Statements";

$cssList = array("/base/css/money.css");
include "base/menu.php";
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 10/16/2020</H5>
<HR size="1">

<p>
    <? include "base/statbar.html" ?>
</p>


<div class="center">

    <?
    $amt_owed = array( 12=>38.74, );

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
            <th>2020 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$474.99</td>
            <td class="padded"><span class="debt">($174.99)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$236.06</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$5.28</td>
            <td class="padded">*</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$10.80</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$61.66</td>
            <td class="padded">$13.34<td>
            <td class="padded">-</td>
            <td class="padded">10</td>
            <td class="padded">4 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$1.04</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$15.70</td>
            <td class="padded">$59.30</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$5.52</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$9.84</td>
            <td class="padded">$65.16</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$487.80</td>
            <td class="padded"><span class="debt">($287.80)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$130.52</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$399.60</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$332.97</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$44.50</td>
            <td class="padded">-</td>
            <td class="padded">$11.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($38.74)</span></td>
            <td class="padded">$38.74</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$237.32</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$167.84</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded"><span class="debt">($4.42)</span></td>
            <td class="padded">$79.98</td>
            <td class="padded">$6.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$8.36</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$420.09</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$353.37</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$236.81</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.76</td>
            <td class="padded">-</td>
            <td class="padded">$167.33</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>* - Adam prepaid for two seasons when joining the league</p>
    <p>Previous column is based on <a href="/history/2019Season/teammoney">2019  results</a></p>

</div>

<? include "base/footer.html"; ?>
