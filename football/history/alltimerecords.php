<?php
require_once 'utils/start.php';

function cmp($a, $b): int
{
    if ($a['pct'] > $b['pct']) {
        return 1;
    } else if ($a['pct'] < $b['pct']) {
        return -1;
    } else if ($a['games'] > $b['games']) {
        return 1;
    } else if ($a['games'] < $b['games']) {
        return -1;
    } else if ($a['wins'] > $b['wins']) {
        return 1;
    } else if ($a['wins'] < $b['wins']) {
        return -1;
    } else {
        return 0;
    }
}

function displayBlock($array, $wties = true)
{
    $count = 0;
    foreach ($array as $team) {
        $disPCT = sprintf('%3.3f', $team['pct']);
        if ($team['active'] == 0) {
            $dec = '<i>';
            $ced = '</i>';
        } else {
            $dec = '';
            $ced = '';
        }

        $count++;
        if ($wties) {
            print <<<EOD
    <TR><TD class="text-left">$dec{$team['name']}$ced</TD>
    <TD>$dec{$team['games']}$ced</TD><TD>$dec{$team['wins']}$ced</TD>
    <TD>$dec{$team['losses']}$ced</TD><TD>$dec{$team['ties']}$ced</TD>
    <TD>$dec$disPCT$ced</TD></TR>
EOD;
        } else {
            print <<<EOD
    <TR><TD class="text-left">$dec{$team['name']}$ced</TD>
    <TD>$dec{$team['games']}$ced</TD><TD>$dec{$team['wins']}$ced</TD>
    <TD>$dec{$team['losses']}$ced</TD><TD>$dec$disPCT$ced</TD></TR>
EOD;
        }
    }
}

function getRecList($addWhere, $season): array
{
    global $conn;
    $alltimeQuery = <<<EOD
        select t.name, t.active, count(s.gameid) as 'games',
        sum(if(t.teamid=s.TeamA, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb>s.scorea, 1, 0))) as 'wins',
        sum(if(t.teamid=s.TeamA, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb<s.scorea, 1, 0))) as 'losses',
        sum(if(s.scorea=s.scoreb, 1, 0)) as 'ties'
        from team t, schedule s
        where t.teamid in (s.TeamA, s.TeamB) and s.season < $season

EOD;
    $groupBy = 'group by t.teamid';

    $finalQuery = $alltimeQuery . ' ' . $addWhere . ' ' . $groupBy;
    $result = mysqli_query($conn, $finalQuery) or die('Dead alltime query: ' . $finalQuery . '<br/>Error: ' . mysqli_error($conn));

    $recordsArray = array();
    while ($team = mysqli_fetch_array($result)) {
        $pct = ($team['wins'] + $team['ties'] / 2.0) / $team['games'];
        $team['pct'] = $pct;
        array_push($recordsArray, $team);
    }
    usort($recordsArray, 'cmp');
    return array_reverse($recordsArray);
}


$allTimeArray = getRecList('', $currentSeason);
$regSeasonArray = getRecList('and postseason=0', $currentSeason);
$postSeasonArray = getRecList('and postseason=1', $currentSeason);
$playoffArray = getRecList('and playoffs=1', $currentSeason);
$championshipArray = getRecList('and championship=1', $currentSeason);
$toiletBowlArray = getRecList('and postseason=1 and playoffs=0', $currentSeason);


$javascriptList = array('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
    '/base/js/jquery.tablesorter.min.js',
    '/base/js/table.base.js');
$title = 'WMFFL ALL-Time Records';
include 'base/menu.php';
?>

<h1 class="text-center">All-Time Win Loss Records</h1>
<h5 class="text-center">Through <?php print $currentSeason - 1; ?> Season</h5>
<hr class="border" />

<div class="container">
    <div class="card m-4">
        <div class="card-header">
            <div class="text-center font-weight-bold h3">Overall Records</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-sm text-center tablesorter tablesorter">
                <thead>
                <tr>
                    <th scope="col" class="text-left">Team</th>
                    <th scope="col">Games</th>
                    <th scope="col">Wins</th>
                    <th scope="col">Losses</th>
                    <th scope="col">Ties</th>
                    <th scope="col">PCT</th>
                </tr>
                </thead>
                <tbody>
                <?php displayBlock($allTimeArray); ?>
                </tbody>
            </TABLE>
        </div>
    </div>

    <div class="card m-4">
        <div class="card-header">
            <div class="text-center font-weight-bold h3">Regular Season Records</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-sm text-center tablesorter">
                <thead>
                <tr>
                    <th scope="col" class="text-left">Team</th>
                    <th scope="col">Games</th>
                    <th scope="col">Wins</th>
                    <th scope="col">Losses</th>
                    <th scope="col">Ties</th>
                    <th scope="col">PCT</th>
                </tr>
                </thead>
                <tbody>
                <?php displayBlock($regSeasonArray); ?>
            </TABLE>
        </div>
    </div>

    <div class="card m-4">
        <div class="card-header">
            <div class="text-center font-weight-bold h3">Post-Season Records</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-sm text-center tablesorter">
                <thead>
                <tr>
                    <th scope="col" class="text-left">Team</th>
                    <th scope="col">Games</th>
                    <th scope="col">Wins</th>
                    <th scope="col">Losses</th>
                    <th scope="col">PCT</th>
                </tr>
                </thead>
                <tbody>
                <?php displayBlock($postSeasonArray, false); ?>
                </tbody>
            </TABLE>
        </div>
    </div>

    <div class="card m-4">
        <div class="card-header">
            <div class="text-center font-weight-bold h3">Playoff Records</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-sm text-center tablesorter">
                <thead>
                <tr>
                    <th scope="col" class="text-left">Team</th>
                    <th scope="col">Games</th>
                    <th scope="col">Wins</th>
                    <th scope="col">Losses</th>
                    <th scope="col">PCT</th>
                </tr>
                </thead>
                <tbody>
                <?php displayBlock($playoffArray, false); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card m-4">
        <div class="card-header">
            <div class="text-center font-weight-bold h3">Championship Game Records</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-sm text-center tablesorter">
                <thead>
                <tr>
                    <th scope="col" class="text-left">Team</th>
                    <th scope="col">Games</th>
                    <th scope="col">Wins</th>
                    <th scope="col">Losses</th>
                    <th scope="col">PCT</th>
                </tr>
                </thead>
                <tbody>
                <?php displayBlock($championshipArray, false); ?>
                </tbody>
            </table>
        </div>
    </div>


    <div class="card m-4">
        <div class="card-header">
            <div class="text-center font-weight-bold h3">Toilet Bowl Game Records</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover table-sm text-center tablesorter">
                <thead>
                <tr>
                    <th scope="col" class="text-left">Team</th>
                    <th scope="col">Games</th>
                    <th scope="col">Wins</th>
                    <th scope="col">Losses</th>
                    <th scope="col">PCT</th>
                </tr>
                </thead>
                <tbody>
                <?php displayBlock($toiletBowlArray, false); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'base/footer.html' ?>
