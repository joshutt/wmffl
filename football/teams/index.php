<?php
include 'utils/start.php';

$title = 'WMFFL Teams';
$cssList = array('/base/css/team.css');

include 'base/menu.php';

$divisionSQL = "SELECT t.teamid, t.name as 'team', d.name as 'division', d.divisionid, u.name as 'owner'
FROM team t
JOIN user u on u.TeamID=t.TeamID
JOIN division d on t.divisionid = d.divisionid
WHERE $currentSeason between d.startYear and d.endYear
ORDER BY d.name, t.name";

$results = mysqli_query($conn, $divisionSQL) or die('Error in query: ' . mysqli_error($conn));
$teamList = array();
while ($teamInfo = mysqli_fetch_array($results)) {
    if (!array_key_exists($teamInfo['division'], $teamList)) {
        $teamList[$teamInfo['division']] = array();
    }
    array_push($teamList[$teamInfo['division']], $teamInfo);
}

?>

<h1 align="center">The Teams</h1>
<hr size = "1">

<div class="row">
    <?php
    ksort($teamList);
    foreach ($teamList as $divisionName => $division) {
        ?>
        <div class="col-12 col-md-4">
            <div class="my-2 shadow card bg-div-<?= $division[0]['divisionid'] ?>">
                <div class="font-weight-bold text-center h3 card-title m-2"><?= $divisionName ?></div>
                <div class="card-body">
                    <ul class="list-group w-100">
                        <?php
                        foreach ($division as $teamInfo) {
                            ?>
                            <a href="/teams/teamroster?viewteam=<?= $teamInfo['teamid'] ?>" class="my-2 shadow list-group-item-action list-group-item">
                            <div class="font-weight-bold"><?= $teamInfo['team'] ?></div>
                            <div class="small"><?= $teamInfo['owner'] ?></div>
                            </a>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<div class="row justify-content-around">
    <div class="col-12 col-md-4">
        <div class="my-2 shadow card">
            <div class="font-weight-bold text-center h3 card-title m-2">Defunct Teams</div>
            <div class="card-body">
                <ul class="list-group w-100">
                    <a href="/teams/squirrels"
                       class="my-2 shadow list-group-item-action list-group-item">
                        <div class="font-weight-bold">Fighting Squirrels</div>
                    </a>
                    <div class="my-2 shadow list-group-item-action list-group-item">
                        <div class="font-weight-bold">Kingsmen</div>
                    </div>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="my-2 shadow card">
            <div class="font-weight-bold text-center h3 card-title m-2">Other Features</div>
            <div class="card-body">
                <ul class="list-group w-100">
                    <a href="/teams/compareteams"
                       class="my-2 shadow list-group-item-action list-group-item">
                        <div class="font-weight-bold">Compare Rosters</div>
                    </a>
                    <a href="/transactions/displayWaiverOrder"
                       class="my-2 shadow list-group-item-action list-group-item">
                        <div class="font-weight-bold">Waiver Wire Order</div>
                    </a>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'base/footer.php'; ?>
