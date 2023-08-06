<?php
require_once 'utils/start.php';

$title = '2020 WMFFL Financial Statements';

$cssList = array('/base/css/money.css');
include 'base/menu.php';
?>


<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 12/30/2020</H5>
<HR size="1">

<p>
    <?php include 'base/statbar.html' ?>
</p>


<div class="center">

    <?php
    $amt_owed = array( );

    if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

        <h2 align="center"><a href="http://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
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
            <th>2021 Fee</th>
        <tr>
        <tr class="oddRow">
            <td class="name padded">Amish Electricians</td>
            <td class="padded">$474.99</td>
            <td class="padded"><span class="debt">($174.99)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">10</td>
            <td class="padded">12 x $3.07</td>
            <td class="padded">Division Title - $25.73<br/>Playoff Team - $25.73<br/>First Round Win - $102.92<br/>Championship - $128.67</td>
            <td class="padded">$534.89</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">British Bulldogs</td>
            <td class="padded">$5.28</td>
            <td class="padded">*</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$20.63</td>
            <td class="padded">$54.37</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Crusaders</td>
            <td class="padded">$61.66</td>
            <td class="padded">$13.34<td>
            <td class="padded">-</td>
            <td class="padded">25</td>
            <td class="padded">9 x $3.07</td>
            <td class="padded">Division Title - $25.73<br/>Playoff Team - $25.73</td>
            <td class="padded">$54.09</td>
            <td class="padded">$20.91</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Fighting Squirrels</td>
            <td class="padded">$15.70</td>
            <td class="padded">$59.30</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">2</td>
            <td class="padded">8 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$22.56</td>
            <td class="padded">$52.44</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Gallic Warriors</td>
            <td class="padded">$9.84</td>
            <td class="padded">$65.16</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">3 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$9.21</td>
            <td class="padded">$65.79</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">MeggaMen</td>
            <td class="padded">$487.80</td>
            <td class="padded"><span class="debt">($287.80)</span></td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">20</td>
            <td class="padded">6 x $3.07</td>
            <td class="padded">Division Title - $25.73<br/>Playoff Team - $25.73</td>
            <td class="padded">$174.88</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Norsemen</td>
            <td class="padded">$399.60</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">14</td>
            <td class="padded">9 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$338.23</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Richard's Lionhearts</td>
            <td class="padded">$44.50</td>
            <td class="padded">$38.74</td>
            <td class="padded">$11.00</td>
            <td class="padded">-</td>
            <td class="padded">20</td>
            <td class="padded">6 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded"><span class="debt">($4.34)</span></td>
            <td class="padded">$79.34</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Sacks On the Beach</td>
            <td class="padded">$237.32</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">11</td>
            <td class="padded">6 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$169.74</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Sean Taylor's Ashes</td>
            <td class="padded"><span class="debt">($4.42)</span></td>
            <td class="padded">$79.98</td>
            <td class="padded">$6.00</td>
            <td class="padded">-</td>
            <td class="padded">7</td>
            <td class="padded">9 x $3.07</td>
            <td class="padded">Playoff Team - $25.73<br/>First Round Win - $102.92</td>
            <td class="padded">$143.84</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="oddRow">
            <td class="name padded">Testudos Revenge</td>
            <td class="padded">$420.09</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">4</td>
            <td class="padded">6 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$359.51</td>
            <td class="padded">$0.00</td>
        </tr>
        <tr class="evenRow">
            <td class="name padded">Trump Molests Collies</td>
            <td class="padded">$236.81</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">-</td>
            <td class="padded">5 x $3.07</td>
            <td class="padded">-</td>
            <td class="padded">$177.16</td>
            <td class="padded">$0.00</td>
        </tr>
    </table>
    <p>* - Adam prepaid for two seasons when joining the league</p>
    <p>Previous column is based on <a href="/history/2019Season/teammoney">2019  results</a></p>

</div>

<?php include 'base/footer.php'; ?>
