<?
require_once "utils/start.php";

$title = "2017 WMFFL Financial Statements";

$cssList = array("/base/css/money.css");
include "base/menu.php";
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 1/13/2018</H5>
<HR size="1">

<p>
    <? include "base/statbar.html" ?>
</p>


<div class="center">

    <?
    $amt_owed = array();

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
            <th>2018 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$366.49</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">9</td>
            <td class="padded">9 x $3.35</td>
            <td class="padded">Division Title - $9.39<br/>Playoffs - $56.36<br/>First Round Win - $84.54<br/>Championship - $140.90</td>
            <td class="padded">$603.83</td>
            <td>$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$16.79</td>
            <td class="padded">$58.21
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">35</td>
            <td class="padded">10 x $3.35</td>
            <td class="padded">Division Title - $9.39<br/>Playoffs - $56.36</td>
            <td class="padded">$64.25</td>
            <td>$10.75</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Fightin' Bitin' Beavers</td>
            <td class="padded">-</td>
            <td class="padded">$150.00*</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">8</td>
            <td class="padded">10 x $3.35</td>
            <td class="padded">Division Title - $9.39<br/>Playoffs - $56.36</td>
            <td class="padded">$166.25</td>
            <td>$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$22.08</td>
            <td class="padded">$52.92</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">7 x $3.35</td>
            <td class="padded">-</td>
            <td class="padded">$19.45</td>
            <td>$55.55</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$92.43</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">13</td>
            <td class="padded">7 x $3.35</td>
            <td class="padded">-</td>
            <td class="padded">$27.88</td>
            <td>$42.12</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$694.12</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">30</td>
            <td class="padded">7 x $3.35</td>
            <td class="padded">-</td>
            <td class="padded">$612.57</td>
            <td>$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded"><span class="debt">($127.35)</span></td>
            <td class="padded">$127.35**<br/>$80.84</td>
            <td class="padded">$11.00</td>
            <td class="padded">-</td>
            <td class="padded">17</td>
            <td class="padded">7 x $3.35</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($4.55)</span></td>
            <td>$206.90</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">-</td>
            <td class="padded">$150.00*</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7</td>
            <td class="padded">3 x $3.35<br/>1 x $1.68</td>
            <td class="padded">-</td>
            <td class="padded">$79.75</td>
            <td>$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$287.15</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">9 x $3.35</td>
            <td class="padded">Playoffs - $56.36<br/>First Round Win - $84.54</td>
            <td class="padded">$379.20</td>
            <td>$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$0.00</td>
            <td class="padded">***</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">21</td>
            <td class="padded">1 x $3.35<br/>1 x $1.68</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($16.97)</span></td>
            <td>$91.97</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Tim Always Pulls Out Late</td>
            <td class="padded">$97.62</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">-</td>
            <td class="padded">6 x $3.35</td>
            <td class="padded">-</td>
            <td class="padded">$40.72</td>
            <td>$34.28</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Woodland Rangers</td>
            <td class="padded">$420.95</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">8</td>
            <td class="padded">7 x $3.35</td>
            <td class="padded">-</td>
            <td class="padded">$361.40</td>
            <td>$0.00</td>
        </tr>
    </table>
    <p>* - The Fightin' Bitin' Beavers and Richard's Lionhearts have new owners that are required to pay two years up
        front.<br/>
        ** - Payment agreement reached with Norsemen<br/>
        *** - Sean Taylor's Ashes were exempted from entry fee due to last minute ownership change.</p>
    <p>Previous column is based on <a href="/history/2016Season/teammoney.php">2016 result</a>s</p>

</div>

<? include "base/footer.html"; ?>
