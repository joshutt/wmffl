<?php

require_once 'utils/start.php';
require_once 'InjuryReportResource.php';

$reportResource = new InjuryReportResource($conn);

$javascriptList = array('//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
    '/base/vendor/js/jquery.tablesorter.min.js',
    '/base/js/injury.js');
$title = 'Injury Report';
include 'base/menu.php';
?>

    <h1 align="center"><?= $title ?></h1>
    <hr/>
<?php include 'base/statbar.html'; ?>

    <div class="mx-auto text-center">
        <button type="button" class="btn btn-wmffl mx-4" id="currentLists-tab" data-toggle="tab"
                onclick="toggleIRCurrent()">Current Lists
        </button>
        <button type="button" class="btn btn-wmffl mx-4" id="fillList-tab" data-toggle="tab" onclick="toggleIRFull()">
            Full
            Report
        </button>
    </div>

    <div class="tab-content">
        <div id="currentLists" class="tab-pane show active">

            <div class="row justify-content-around">
                <div class="card px-0 m-2">
                    <div class="card-header text-center font-weight-bold">Current IR List</div>
                    <div class="card-body">
                        <table class="table table-hover mb-0 tablesorter" id="irListTable">
                            <thead>
                            <tr>
                                <th>Player</th>
                                <th>Pos</th>
                                <th>NFL</th>
                                <th>Team</th>
                                <th>Reason</th>
                                <th>On IR Since</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $irList = $reportResource->getIRList();
                            if (sizeof($irList) == 0) {
                                ?>
                                <tr>
                                    <td colspan="6" class="font-weight-bold">No Players Current on IR</td>
                                </tr>
                                <?php
                            }
                            foreach ($irList as $player) {
                                ?>
                                <tr>
                                    <td><?= $player->firstname ?> <?= $player->lastname ?></td>
                                    <td><?= $player->pos ?></td>
                                    <td><?= $player->nflteamid ?></td>
                                    <td><?= $player->abbrev ?></td>
                                    <td><?= $player->details ?></td>
                                    <td><?= date_format(new DateTime($player->dateon), 'n-d-Y') ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row justify-content-around">

                <div class="card px-0 m-2">
                    <div class="card-header text-center font-weight-bold">Current Covid List</div>
                    <div class="card-body">

                        <table class="table table-hover mb-0 tablesorter">
                            <thead>
                            <tr>
                                <th>Player</th>
                                <th>Pos</th>
                                <th>NFL</th>
                                <th>Team</th>
                                <th>Date On</th>
                                <th>Exp Return</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $covidList = $reportResource->getCovidList();
                            if (sizeof($covidList) == 0) {
                                ?>
                                <tr>
                                    <td colspan="6" class="font-weight-bold">No Players Current on Covid List</td>
                                </tr>
                                <?php
                            }
                            foreach ($covidList as $player) {
                                ?>
                                <tr>
                                    <td><?= $player->firstname ?> <?= $player->lastname ?></td>
                                    <td><?= $player->pos ?></td>
                                    <td><?= $player->nflteamid ?></td>
                                    <td><?= $player->abbrev ?></td>
                                    <td><?= date_format(new DateTime($player->dateon), 'n-d-Y') ?></td>
                                    <td><?= date_format(new DateTime($player->expectedReturn), 'n-d-Y') ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <div class="row justify-content-around">

                <div class="card px-0 m-2">
                    <div class="card-header text-center font-weight-bold">IR Eligible</div>
                    <div class="card-body">
                        <table class="table table-hover mb-0 tablesorter">
                            <thead>
                            <tr>
                                <th>Player</th>
                                <th>Pos</th>
                                <th>NFL</th>
                                <th>Team</th>
                                <th>Reason</th>
                                <th>Exp Return</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $irEligible = $reportResource->getEligible();
                            if (sizeof($irEligible) == 0) {
                                ?>
                                <tr>
                                    <td colspan="6" class="font-weight-bold">No Players Current Eligible for IR</td>
                                </tr>
                                <?php
                            }
                            foreach ($irEligible as $player) {
                                ?>
                                <tr>
                                    <td><?= $player->firstname ?> <?= $player->lastname ?></td>
                                    <td><?= $player->pos ?></td>
                                    <td><?= $player->nflteamid ?></td>
                                    <td><?= $player->abbrev ?></td>
                                    <td><?= $player->details ?></td>
                                    <td><?= date_format(new DateTime($player->expectedReturn), 'n-d-Y') ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="fullList" class="tab-pane container">
            <div class="row justify-content-between">
                <?php
                $allTeams = $reportResource->getFullReport();
                $first = true;
                foreach ($allTeams as $name => $inj) {
                    ?>
                    <?php if ($first) { ?>
                        <h3 class="col-12 text-center mt-2">League Wide Injury Report for Week <?= $inj[0]->week ?></h3>
                        <?php
                        $first = false;
                    } ?>
                    <div class="card px-0 m-2">
                        <div class="card-header text-center font-weight-bold"><?= $name ?></div>
                        <div class="card-body">
                            <table class="table table-hover tablesorter">
                                <thead>
                                <tr>
                                    <th>Player</th>
                                    <th>Pos</th>
                                    <th>NFL</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($inj as $player) {
                                    ?>
                                    <tr>
                                        <td><?= $player->firstname ?> <?= $player->lastname ?></td>
                                        <td><?= $player->pos ?></td>
                                        <td><?= $player->nflteamid ?></td>
                                        <td><?= $player->status ?></td>
                                        <td><?= $player->details ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

<?php
include 'base/footer.php';
