<?php
//namespace transactions\injury;
//use InjuryPlayer;
require_once 'InjuryPlayer.php';
require_once 'utils/reportUtils.php';

$title = "Injury Report";
include "base/menu.php";

// Start with current season and week as default
$evalSeason = $currentSeason;
$evalWeek = $currentWeek;

// Use passed in season or week if available
if (isset($_REQUEST["season"])) {
    $evalSeason = $_REQUEST['season'];
}
if (isset($_REQUEST["week"])) {
    $evalWeek = $_REQUEST['week'];
}


// SQL Statement
$sql = "select p.firstname, p.lastname, p.pos, nr.nflteamid as 'nflTeam', tn.abbrev as 'team', i.season, i.week, i.status, i.details, i.expectedReturn
from newinjuries i
join newplayers p on i.playerid = p.playerid
join weekmap wm on wm.season=i.season and wm.Week=i.week
left join roster r on r.PlayerID=p.playerid and r.DateOn <= wm.ActivationDue and (r.DateOff is null or r.DateOff>=wm.ActivationDue)
left join nflrosters nr on p.playerid=nr.playerid and nr.dateon<=wm.ActivationDue and (nr.dateoff is null or nr.dateoff>=wm.ActivationDue)
left join teamnames tn on r.TeamID=tn.teamid and tn.season=wm.Season
where wm.season=? and wm.week=?
order by i.status
";

// Execute the query
$stmt = $conn->prepare($sql) or die("Error: " . mysqli_error($conn));
$stmt->bind_param('ii', $evalSeason, $evalWeek);
$stmt->execute() or die("Error: " . mysqli_error($conn));
$results = $stmt->get_result();

try {
    $injuries = array();
    while ($row = mysqli_fetch_assoc($results)) {
        $status = $row['status'];
        if (!array_key_exists($status, $injuries)) {
            $injuries[$status] = array();
        }
        $injPlayer = \transactions\injury\InjuryPlayer::loadAssocArray($row);
        array_push($injuries[$status], $injPlayer);
    }
} catch (\Exception $e) {
    echo 'Message: ' . $e->getMessage();
}


// Get COVID IR
$covidIR = $injuries['COVID-IR'];
$optOut = $injuries['Holdout'];

$IRList = array_merge($injuries['IR'], $injuries['IR-NFI'], $injuries['IR-PUP']);

array_reduce()

print_r(array_keys($injuries));

// Get IR
// Get Injuries
$injArr = array();
foreach ($IRList as $player) {
    $newItem = array($player->getFirstName(), $player->getLastName());
    array_push($injArr, $newItem);
}

?>

    <div class="container">
        <div class="row"><?= $title ?> - <?= $evalSeason ?> - <?= $evalWeek ?></div>
        <div class="row m-2">
            <button class="button mx-2 p-1">COVID List</button>
            <button class="button mx-2 p-1">IR</button>
            <button class="button mx-2 p-1">Full List</button>
        </div>
        <div class="row m-2">
            <button class="button mx-2 p-1">By WMFFL Team</button>
            <button class="button mx-2 p-1">By NFL Team</button>
        </div>
    </div>

    <div class="row" id="covidList">
        <div class="col">
            <div class="container-fluid">
                <div class="row justify-content-around">
                    <div class="col text-center"><h4>Opt-Outs</h4></div>
                </div>
                <?php
                foreach ($optOut as $player) {
                    ?>
                    <div class="row justify-content-around">
                        <div class="col-4"><?= $player->getFirstName() ?> <?= $player->getLastName() ?></div>
                        <div class="col-1"><?= $player->getPos() ?></div>
                        <div class="col-1"><?= $player->getNflTeam() ?></div>
                        <div class="col-1"><?= $player->getTeam() ?></div>
                        <div class="col-3"><?= $player->getStatus()->getDetails() ?></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="col">
            <div class="container-fluid">
                <div class="row justify-content-between">
                    <div class="col text-center"><h4>COVID IR</h4></div>
                </div>
                <?php
                foreach ($covidIR as $player) {
                    ?>
                    <div class="row justify-content-around">
                        <div class="col-4"><?= $player->getFirstName() ?> <?= $player->getLastName() ?></div>
                        <div class="col-1"><?= $player->getPos() ?></div>
                        <div class="col-1"><?= $player->getNflTeam() ?></div>
                        <div class="col-1"><?= $player->getTeam() ?></div>
                        <div class="col-3"><?= $player->getStatus()->getDetails() ?></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="container-fluid">
                <div class="row justify-content-around">
                    <div class="col text-center"><h4>IR List</h4></div>
                </div>
                <?php
                foreach ($IRList as $player) {
                    ?>
                    <div class="row justify-content-around">
                        <div class="col"><?= $player->getFirstName() ?> <?= $player->getLastName() ?></div>
                        <div class="col-1"><?= $player->getPos() ?></div>
                        <div class="col-1"><?= $player->getNflTeam() ?></div>
                        <div class="col-1"><?= $player->getTeam() ?></div>
                        <div class="col-1"><?= $player->getStatus()->getStatus() ?></div>
                        <div class="col"><?= $player->getStatus()->getDetails() ?></div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

<?php
include "base/footer.html";