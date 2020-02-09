<?php
require_once "utils/start.php";

$title = "2019 WMFFL Financial Statements";

$cssList = array("/base/css/money.css");
include "base/menu.php";
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 2/9/2020</H5>
<HR size="1">

<p>
    <? include "base/statbar.html" ?>
</p>


<div class="center">

    <?
    $amt_owed = array( );

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
            <td class="padded">$533.01</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5</td>
            <td class="padded">7 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$474.99</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">-</td>
            <td class="padded">$150.00</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">-</td>
            <td class="padded">2 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$5.28</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$3.30</td>
            <td class="padded">$83.20</td>
            <td class="padded">$11.00</td>
            <td class="padded">1</td>
            <td class="padded">22</td>
            <td class="padded">10 x $3.14</td>
            <td class="padded">Division Title - $26.38<br/>Playoffs - $26.38</td>
            <td class="padded">$61.66</td>
            <td class="padded">$13.34</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$5.18</td>
            <td class="padded">$69.82</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$15.70</td>
            <td class="padded">$59.30</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$3.83</td>
            <td class="padded">$71.17</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">8</td>
            <td class="padded">6 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$9.84</td>
            <td class="padded">$65.16</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$559.68</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">22</td>
            <td class="padded">8 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$487.80</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$176.06</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">23</td>
            <td class="padded">10 x $3.14</td>
            <td class="padded">Division Title - $26.38<br/>Playoffs - $26.38<br/>First Round Win - $105.50<br/>Championship - $131.88</td>
            <td class="padded">$399.60</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$26.17</td>
            <td class="padded">$48.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7</td>
            <td class="padded">8 x $3.14</td>
            <td class="padded">Playoffs - $26.38</td>
            <td class="padded">$44.50</td>
            <td class="padded">$30.50</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$308.62</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">12</td>
            <td class="padded">5 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$237.32</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$13.95</td>
            <td class="padded">$68.93</td>
            <td class="padded">$16.00</td>
            <td class="padded">4</td>
            <td class="padded">8</td>
            <td class="padded">5 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($4.42)</span></td>
            <td class="padded">$79.42</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$306.29</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">11 x $3.14</td>
            <td class="padded">Division Title - $26.38<br/>Playoffs - $26.38<br/>First Round Win - $105.50</td>
            <td class="padded">$420.09</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$299.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">10</td>
            <td class="padded">7 x $3.14</td>
            <td class="padded">-</td>
            <td class="padded">$236.81</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2018Season/teammoney">2018  results</a></p>

</div>

<? include "base/footer.html"; ?>
