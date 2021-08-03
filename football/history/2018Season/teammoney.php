<?
require_once "utils/start.php";

$title = "2018 WMFFL Financial Statements";

$cssList = array("/base/css/money.css");
include "base/menu.php";
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 12/28/2018</H5>
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
            <th>2019 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$603.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">3</td>
            <td class="padded">3 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$533.01</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$64.25</td>
            <td class="padded">$10.75</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">11</td>
            <td class="padded">5 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$3.30</td>
            <td class="padded">$71.70</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Fightin' Bitin' Beavers</td>
            <td class="padded">$166.25<td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3</td>
            <td class="padded">9.5 x $3.06</td>
            <td class="padded">Division Title - $25.57<br/>Playoffs - $25.57</td>
            <td class="padded">$168.45</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$19.45</td>
            <td class="padded">$55.55</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">-</td>
            <td class="padded">3 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$5.18</td>
            <td class="padded">$69.82</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$27.88</td>
            <td class="padded">$42.12</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">13</td>
            <td class="padded">5.5 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$3.83</td>
            <td class="padded">$71.17</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$612.57</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">31</td>
            <td class="padded">9 x $3.06</td>
            <td class="padded">Playoffs - $25.57</td>
            <td class="padded">$559.68</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded"><span class="debt">($131.90)</span></td>
            <td class="padded">$206.90</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">11</td>
            <td class="padded">11 x $3.06</td>
            <td class="padded">Division Title - $25.57<br/>Playoffs - $25.57<br/>First Round Win - $102.27</td>
            <td class="padded">$176.06</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$79.75</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$26.17</td>
            <td class="padded">$48.83</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$379.20</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">17</td>
            <td class="padded">7 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$308.62</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded"><span class="debt">($16.97)</span></td>
            <td class="padded">$91.97</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">8</td>
            <td class="padded">7.5 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$13.95</td>
            <td class="padded">$61.05</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$361.40</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">6.5 x $3.06</td>
            <td class="padded">-</td>
            <td class="padded">$306.29</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$40.72</td>
            <td class="padded">$34.28</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">12</td>
            <td class="padded">10 x $3.06</td>
            <td class="padded">Division Title - $25.57<br/>Playoffs - $25.57<br/>First Round Win - $102.27<br/>Championship
                - $127.83
            </td>
            <td class="padded">$299.83</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2017Season/teammoney.php">2017 results</a></p>

</div>

<? include "base/footer.php"; ?>
