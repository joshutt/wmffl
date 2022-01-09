<?php
$title = '2021 WMFFL Season';
include 'base/menu.php';
?>

<H1 ALIGN=CENTER>The 2021 Season</H1>
<HR size = "1">
<br/>

<div class="container">
    <ul class="nav nav-pills nav-fill col my-2 py-1">
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="schedule">Schedule</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/transactions/showprotections?season=2021">Protections</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/stats/leaders?season=2021">League Leaders</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="draftresults">Draft Results</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="#playoffs">Playoffs</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/transactions/transactions?year=2021">Transactions</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="#standings">Final Standings</a></li>
    </ul>
</div>


<HR size = "1">
<A NAME="playoffs"/>

<div class="container align-content-center">
<div class="row justify-content-around align-items-center mb-2">
    <div class="align-self-center">
        <h4>League Champions</h4><br/>
        <b>Norsemen</b>
    </div>
</div>

    <div class="row justify-content-between">
<TABLE class="col">
<TH>Playoffs</TH>
<tr><td><B>Game 1</B></td><td></td><td></td><td><B>Championship</B></td></tr>
<tr><td>MeggaMen</td><td>24</td><td WIDTH=30%></td><td>Norsemen</td><td>49</td></tr>
<tr><td>Sean Taylor's Ashes</td><td>0</td><td WIDTH=30%></td><td>MeggaMen</td><td>40</td></tr>
<tr class="my-2 py-2"><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
<tr><td><B>Game 2</B></td><td></td><td></td><td><B>Toilet Bowl</B></td></tr>
<tr><td>Norsemen</td><td>46</td><td WIDTH=30%></td><td>Gallic Warriors</td><td>24</td></tr>
<tr><td>Trump Molests Collies</td><td>3</td><td WIDTH=30%></td><td>Sacks on the Beach</td><td>11</td></tr>
</TABLE><br/>
    </div>
</div>
<br/>

<HR size = "1">
    <A NAME="standings"/>

<div class="container align-content-center">
    <div class="row justify-content-around">
<?php
$thisSeason = 2021;
$thisWeek = 17;
$clinchedList = array('Gallic Warriors' => 't-', 'Sacks on the Beach' => 't-', 'Norsemen' => 'y-',  'Sean Taylor\'s Ashes' => 'x-', 'Trump Molests Collies' => 'y-', 'MeggaMen' => 'y-');
include 'history/common/weekstandings.php';
?>
</div>
</div>

<?php include 'base/footer.html'; ?>
