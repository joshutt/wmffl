<?php
require_once 'utils/start.php';

$title = '2022 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 10/21/2022</H5>
<HR size="1">

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">

    <?php
    $amt_owed = array(  6 => 84.11);

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
            <th>2022 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$235.61</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$171.69</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$21.14</td>
            <td class="padded">$53.86</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$13.85</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$0.12</td>
            <td class="padded">-</td>
            <td class="padded">$11.00</td>
            <td class="padded">1</td>
            <td class="padded">-</td>
            <td class="padded">1 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($84.11)</span></td>
            <td class="padded">$84.11</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$18.12</td>
            <td class="padded">$56.88</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$5.54</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$15.61</td>
            <td class="padded">$59.39</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">1</td>
            <td class="padded">2 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$4.54</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$260.50</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5</td>
            <td class="padded">3.5 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$190.20</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$549.83</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">7</td>
            <td class="padded">4 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$478.91</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$10.12</td>
            <td class="padded">$70.88</td>
            <td class="padded">$6.00</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$5.54</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$93.84</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$27.15</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded">$101.40</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$34.71</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$300.63</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$231.17</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$177.12</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4.5 x $2.77</td>
            <td class="padded">-</td>
            <td class="padded">$114.59</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>Previous column is based on <a href="/history/2021Season/teammoney">2021  results</a></p>

</div>

<?php include 'base/footer.php'; ?>
