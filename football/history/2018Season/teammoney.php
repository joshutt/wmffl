<?
require_once "utils/start.php";

$title = "2018 WMFFL Financial Statements";

$cssList = array("/base/css/money.css");
include "base/menu.php";
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 8/29/2018</H5>
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
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$603.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$528.83</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$64.25</td>
            <td class="padded">$10.75</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Fightin' Bitin' Beavers</td>
            <td class="padded">$166.25<td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$91.25</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$19.45</td>
            <td class="padded">$55.55</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$27.88</td>
            <td class="padded">$42.12</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$612.57</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$537.57</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded"><span class="debt">($131.90)</span></td>
            <td class="padded">$206.90</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$79.75</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$4.75</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$379.20</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$304.20</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded"><span class="debt">($16.97)</span></td>
            <td class="padded">$91.97<td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Tim Always Pulls Out Late</td>
            <td class="padded">$40.72</td>
            <td class="padded">$34.28<td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Woodland Rangers</td>
            <td class="padded">$361.40</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">$286.40</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2017Season/teammoney.php">2017 results</a></p>

</div>

<? include "base/footer.html"; ?>
