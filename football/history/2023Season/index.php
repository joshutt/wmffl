<?php
$title = '2023 WMFFL Season';
include 'base/menu.php';
?>

<H1 ALIGN=CENTER>The 2023 Season</H1>
<HR size = "1">
<br/>

<div class="container">
    <ul class="nav nav-pills nav-fill col my-2 py-1">
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="schedule">Schedule</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/transactions/showprotections?season=2023">Protections</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/stats/leaders?season=2023">League Leaders</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="draftresults">Draft Results</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2 d-none"><a class="nav-link" href="#playoffs">Playoffs</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/transactions/transactions?year=2023">Transactions</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="standings">Standings</a></li>
    </ul>
</div>


<div class="container align-content-center">
<div class="row justify-content-around align-items-center mb-2 d-none">
<HR size = "1">
<A NAME="playoffs"/>

    <div class="align-self-center">
        <h4>League Champions</h4><br/>
        <b>Trump Molests Collies</b>
    </div>
</div>

    <div class="row justify-content-between d-none">
<TABLE class="col">
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>Trump Molests Collies</td><td>45</td><td WIDTH=30%></td><td>Trump Molests Collies</td><td>48</td></tr>
<tr><td>Norsemen</td><td>41</td><td WIDTH=30%></td><td>MeggaMen</td><td>42</td></tr>
<tr class="my-2 py-2"><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>MeggaMen</td><td>76</td><td WIDTH=30%></td><td>Crusaders</td><td>56</td></tr>
<tr><td>Testudos Revenge</td><td>19</td><td WIDTH=30%></td><td>Richard's Lionhearts</td><td>27</td></tr>
</TABLE><br/>
    </div>
</div>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<div class="container align-content-center">
    <div class="row justify-content-around">
<?php
$thisSeason = 2023;
//$thisWeek = 14;
$thisWeek = $currentWeek;
$clinchedList = array();
include 'history/common/weekstandings.php';
?>
</div>
</div>

<?php include 'base/footer.php'; ?>
