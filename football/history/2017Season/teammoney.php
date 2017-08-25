<?
require_once "utils/start.php";

$title = "2017 WMFFL Financial Statements";

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
<H5 ALIGN=Center>Last Updated 8/17/2017</H5>
<HR size = "1">

<p>
<? include "base/statbar.html" ?>
</p>


<div class="center">

<?  
$amt_owed = array( 3 => "202.37", 4 => "23.03");

if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

<h2 align="center"><a href="http://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
<? } ?>

<table class="report">
<tr class="titleRow"><th>Team</th><th>Previous</th><th>Paid</th><th>Late Fees</th><th>Illegal<br/>Lineup</th><th>Extra<br/>Transactions</th><th>Wins</th><th>Playoffs</th><th>Balance</th><th>2017 Fee</th><tr>
<tr class="oddRow"><td class="name padded">Amish Electricians</td><td class="padded">$366.49</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$291.49</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Crusaders</td><td class="padded">$16.79</td><td class="padded">$58.21<td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$0.00</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Fightin' Bitin' Beavers</td><td class="padded">-</td><td class="padded">$150.00*</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$0.00</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Fighting Squirrels</td><td class="padded">$22.08</td><td class="padded">$52.92</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$0.00</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Gallic Warriors</td><td class="padded">$92.43</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$17.43</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">MeggaMen</td><td class="padded">$694.12</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$619.12</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Norsemen</td><td class="padded"><span class="debt">($127.35)</span></td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded"><span class="debt">($202.35)</span></td><td>$202.35</td></tr>
<tr class="evenRow"><td class="name padded">Richard's Lionhearts</td><td class="padded">-</td><td class="padded">$150.00*</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$0.00</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Sacks On the Beach</td><td class="padded">$287.15</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$212.15</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Sean Taylor's Ashes</td><td class="padded">$51.97</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded"><span class="debt">($23.03)</span></td><td>$23.03</td></tr>
<tr class="oddRow"><td class="name padded">Whiskey Tango</td><td class="padded">$97.62</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$22.62</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Woodland Rangers</td><td class="padded">$420.95</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">$345.95</td><td>$0.00</td></tr>
</table>
<p>* - The Fightin' Bitin' Beavers and Richard's Lionhearts have new owners that are required to pay two years up front.</p>
<p>Previous column is based on <a href="/history/2016Season/teammoney.php">2016 result</a>s</p>

</div>

<? include "base/footer.html"; ?>
