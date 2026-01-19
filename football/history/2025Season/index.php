<?php
$title = '2025 WMFFL Season';
include 'base/menu.php';
?>

<h1 class="text-center">The 2025 Season</h1>
<hr class="my-2">
<br/>

<div class="container">
    <ul class="nav nav-pills nav-fill col my-2 py-1">
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="schedule">Schedule</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link"
                                                             href="/transactions/showprotections?season=2025">Protections</a>
        </li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="/stats/leaders?season=2025">League
                Leaders</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="draftresults">Draft Results</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="#playoffs">Playoffs</a></li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link"
                                                             href="/transactions/transactions?year=2025">Transactions</a>
        </li>
        <li class="nav-item col-6 col-md-4 col-lg-3 my-2"><a class="nav-link" href="standings">Final Standings</a></li>
    </ul>
</div>

<div class="container align-content-center">
    <hr class="my-2">
    <a id="playoffs"></a>

    <div class="row justify-content-around align-items-center mb-2">

        <div class="align-self-center">
            <h4>League Champions</h4><br/>
            <b>Gallic Warriors</b>
        </div>
    </div>

    <div class="row justify-content-between">
        <table class="col">
            <thead>
            <tr>
                <th>Playoffs</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><b>Game 1</b></td>
                <td></td>
                <td></td>
                <td><b>Championship</b></td>
            </tr>
            <tr>
                <td>Gallic Warriors</td>
                <td>76</td>
                <td style="width: 30%"></td>
                <td>Gallic Warriors</td>
                <td>32</td>
            </tr>
            <tr>
                <td>Norsemen</td>
                <td>40</td>
                <td style="width: 30%"></td>
                <td>MeggaMen</td>
                <td>16</td>
            </tr>
            <tr class="my-2 py-2">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><b>Game 2</b></td>
                <td></td>
                <td></td>
                <td><b>Toilet Bowl</b></td>
            </tr>
            <tr>
                <td>MeggaMen</td>
                <td>75</td>
                <td style="width: 30%"></td>
                <td>Omar's Coming</td>
                <td>73</td>
            </tr>
            <tr>
                <td>Richard's Lionhearts</td>
                <td>37</td>
                <td style="width: 30%"></td>
                <td>British Bulldogs</td>
                <td>0</td>
            </tr>
            </tbody>
        </table>
        <br/>
    </div>
</div>
<br/>

<hr class="my-2">
<a id="standings"></a>

<div class="container align-content-center">
    <div class="row justify-content-around">
        <?php
        $thisSeason = 2025;
        $thisWeek = 14;
        $clinchedList = array();
        include 'history/common/weekstandings.php';
        ?>
    </div>
</div>

<?php include 'base/footer.php'; ?>
