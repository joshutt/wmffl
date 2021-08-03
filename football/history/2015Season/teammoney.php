<?
require_once "utils/start.php";

$title = "2015 WMFFL Financial Statements";

include "base/menu.php";
?>

<style>
    tr.account1 {background-color: #eeeeee; }
    tr.account2 {background-color: #ffffff; }
    tr.summary {background-color: #cccccc; }
    td.details {font-size: 8pt; text-align: center;}

    .center { text-align: center; }
    .teamList {display: none;}
    .debt {color: red;}

    #crusaders {display: none;}

    .report {
	border-spacing: 1;
	border: 1px solid #000;
	text-align: center;
	margin-left: auto;
	margin-right: auto;
     }

    .titleRow {
	background: #600;
	color: #e2a500;
	font-style: normal;
	font-weight: normal;
    }

    .name { 
	text-align: left;
    }

    .evenRow {
	background: #e0e0e0;
	padding-top: 5px;
	padding-bottom: 5px;
    }

    .report {
	padding: 5px;
    }

    td.padded {
	padding: 5px 10px;
    }


</style>

<script language="javascript">

    function showDetails(name) {

        var e = document.getElementById(name);
        if (e.style.display == "none" || e.style.display == "") {
            e.style.display = "block";
        } else {
            e.style.display = "none";
        }

    }
    
</script>

<H1 ALIGN=Center>Team Finances</H1>
<H5 ALIGN=Center>Last Updated 2/6/2016</H5>
<HR size = "1">

<p>
<? include "base/statbar.html" ?>
</p>

<div class="center">
<table class="report">
<tr class="titleRow"><th>Team</th><th>Previous</th><th>Paid</th><th>Illegal<br/>Lineup</th><th>Extra<br/>Transactions</th><th>Wins</th><th>Playoffs</th><th>Balance</th><th>2016 Fee</th><tr>
<tr class="oddRow"><td class="name padded">Amish Electricians</td><td class="padded">$408.38</td><td class="padded">-</td><td class="padded">-</td><td class="padded">13</td><td class="padded">6 x $3.64</td><td class="padded">-</td><td class="padded">$342.22</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Crusaders</td><td class="padded">$75.77</td><td class="padded">-</td><td class="padded">-</td><td class="padded">25</td><td class="padded">11 x $3.64</td><td class="padded">Division Title - $10.21<br/>Playoffs - $61.17</td><td class="padded">$87.19</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Fighting Squirrels</td><td class="padded">$97.40</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">8 x $3.64</td><td class="padded">-</td><td class="padded">$51.52</td><td>$23.48</td></tr>
<tr class="evenRow"><td class="name padded">Gallic Warriors</td><td class="padded">$94.01</td><td class="padded">-</td><td class="padded">-</td><td class="padded">12</td><td class="padded">5 x $3.64</td><td class="padded">-</td><td class="padded">$25.21</td><td>$49.79</td></tr>
<tr class="oddRow"><td class="name padded">Mansfield Onanists</td><td class="padded"><span class="debt">($65.21)</span></td><td class="padded">$135.33</td><td class="padded">2</td><td class="padded">14</td><td class="padded">6 x $3.64</td><td class="padded">-</td><td class="padded">$0.96</td><td>$74.04</td></tr>
<tr class="evenRow"><td class="name padded">MeggaMen</td><td class="padded">$303.50</td><td class="padded">-</td><td class="padded">-</td><td class="padded">9</td><td class="padded">12 x $3.64</td><td class="padded">Division Title - $10.21<br/>Playoffs - $61.17<br/>First Round Win - $91.75<br/>Championship - $152.92</td><td class="padded">$579.22</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Norsemen</td><td class="padded">$74.91</td><td class="padded">-</td><td class="padded">-</td><td class="padded">37</td><td class="padded">7 x $3.64</td><td class="padded">-</td><td class="padded"><span class="debt">($11.61)</span></td><td>$86.61</td></tr>
<tr class="evenRow"><td class="name padded">Pretend I'm Not Here</td><td class="padded">$51.00</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">5 x $3.64</td><td class="padded">-</td><td class="padded"><span class="debt">($5.80)</span></td><td>$80.80</td></tr>
<tr class="oddRow"><td class="name padded">Sacks On the Beach</td><td class="padded">$265.51</td><td class="padded">-</td><td class="padded">-</td><td class="padded">19</td><td class="padded">8 x $3.64</td><td class="padded">Playoffs - $61.17<br/>First Round Win - $91.75</td><td class="padded">$353.55</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Sean Taylor's Ashes</td><td class="padded">$13.74</td><td class="padded">$75.00</td><td class="padded">1</td><td class="padded">-</td><td class="padded">8 x $3.64</td><td class="padded">Division Title - $10.21<br/>Playoffs - $61.17</td><td class="padded">$113.25</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Whiskey Tango</td><td class="padded">$216.62</td><td class="padded">-</td><td class="padded">-</td><td class="padded">1</td><td class="padded">3 x $3.64</td><td class="padded">-</td><td class="padded">$151.54</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Woodland Rangers</td><td class="padded"><span class="debt">($55.31)</span></td><td class="padded">-</td><td class="padded">2</td><td class="padded">10</td><td class="padded">5 x $3.64</td><td class="padded">-</td><td class="padded"><span class="debt">($124.11)</span></td><td>$199.11</td></tr>
</table>
</div>

<? include "base/footer.php"; ?>
