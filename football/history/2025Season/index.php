<?php
$title = '2025 WMFFL Season';
include 'base/menu.php';
?>

<h1 align=center>The 2025 Season</H1>
<HR size="1">
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

<div class="container align-content-center d-none">
    <hr size="1">
    <a name="playoffs"/>

    <div class="row justify-content-around align-items-center mb-2">

        <div class="align-self-center">
            <h4>League Champions</h4><br/>
            <b></b>
        </div>
    </div>

    <div class="row justify-content-between">
        <TABLE class="col">
            <TH>Playoffs</TH>
            <tr>
                <td><B>Game 1</B></td>
                <td></td>
                <td></td>
                <td><B>Championship</B></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td width=30%></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td width=30%></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="my-2 py-2">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><B>Game 2</B></td>
                <td></td>
                <td></td>
                <td><B>Toilet Bowl</B></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td width=30%></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td width=30%></td>
                <td></td>
                <td></td>
            </tr>
        </TABLE>
        <br/>
    </div>
</div>
<br/>

<hr size="1">
<a name="standings"/>

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
