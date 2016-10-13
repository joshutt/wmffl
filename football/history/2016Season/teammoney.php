<?
require_once "utils/start.php";

$title = "2016 WMFFL Financial Statements";

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
<H5 ALIGN=Center>Last Updated 10/4/2016</H5>
<HR size = "1">

<p>
<? include "base/statbar.html" ?>
</p>


<div class="center">

<?  
$amt_owed = array( 3 => "97.50" );

if ($isin && array_key_exists($teamnum, $amt_owed)) { ?>

<h2 align="center"><a href="http://paypal.me/JoshUtterback/<?= $amt_owed[$teamnum] ?>">Pay Now</a></h2>
<? } ?>

<table class="report">
<tr class="titleRow"><th>Team</th><th>Previous</th><th>Paid</th><th>Late Fees</th><th>Illegal<br/>Lineup</th><th>Extra<br/>Transactions</th><th>Wins</th><th>Playoffs</th><th>Balance</th><th>Current Fee</th><tr>
<tr class="oddRow"><td class="name padded">Amish Electricians</td><td class="padded">$342.22</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">2 x $2.74</td><td class="padded">-</td><td class="padded">$272.70</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Crusaders</td><td class="padded">$87.19</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">3 x $2.74</td><td class="padded">-</td><td class="padded">$20.41</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Fighting Squirrels</td><td class="padded">$51.52</td><td class="padded">$23.48</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">1 x $2.74</td><td class="padded">-</td><td class="padded">$2.74</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Gallic Warriors</td><td class="padded">$25.21</td><td class="padded">$49.79</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">2.5 x $2.74</td><td class="padded">-</td><td class="padded">$6.85</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Mansfield Onanists</td><td class="padded">$0.96</td><td class="padded">$74.04</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">1 x $2.74</td><td class="padded">-</td><td class="padded">$2.74</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">MeggaMen</td><td class="padded">$579.22</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">4 x $2.74</td><td class="padded">-</td><td class="padded">$515.18</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Norsemen</td><td class="padded"><span class="debt">($11.61)</span></td><td class="padded">-</td><td class="padded"><span class="debt">($11.00)</span></td><td class="padded">-</td><td class="padded">4</td><td class="padded">1.5 x $2.74</td><td class="padded">-</td><td class="padded"><span class="debt">($97.50)</span></td><td>$97.50</td></tr>
<tr class="evenRow"><td class="name padded">Pretend I'm Not Here</td><td class="padded"><span class="debt">($5.80)</span></td><td class="padded">$80.80</td><td class="padded">-</td><td class="padded">3</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded"><span class="debt">($3.00)</span></td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Sacks On the Beach</td><td class="padded">$353.55</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">1</td><td class="padded">4 x $2.74</td><td class="padded">-</td><td class="padded">$288.51</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Sean Taylor's Ashes</td><td class="padded">$113.25</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">2 x $2.74</td><td class="padded">-</td><td class="padded">$43.73</td><td>$0.00</td></tr>
<tr class="oddRow"><td class="name padded">Whiskey Tango</td><td class="padded">$151.54</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">1 x $2.74</td><td class="padded">-</td><td class="padded">$79.28</td><td>$0.00</td></tr>
<tr class="evenRow"><td class="name padded">Woodland Rangers</td><td class="padded">-</td><td class="padded">$150.00*</td><td class="padded">-</td><td class="padded">-</td><td class="padded">-</td><td class="padded">2 x $2.74</td><td class="padded">-</td><td class="padded">$5.48</td><td>$0.00</td></tr>
</table>
<p>* - The Woodland Rangers paid what they owed after the 2015 season then the owner resigned.  The new owner will be required to pay two year entry fee up front.</p>
<p>Previous column is based on <a href="/history/2015Season/teammoney.php">2015 result</a>s</p>

</div>

<? include "base/footer.html"; ?>
