<?php
require_once "utils/start.php";
require_once "IRResource.php";

$resource = new IRResource($conn, $teamnum);
$resource->loadElgiblePlayers();
$resource->loadCurrentIRPlayers();

$title = "Injured Reserve";
$javascriptList = array("//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js", "/base/js/injury.js");
include "base/menu.php";
?>

    <h1 align="center">Injured Reserve</h1>
    <hr/>

<?php
include "transmenu.php";

if (!$isin) {
    ?>
    <div class="text-center font-weight-bold h4">You must be logged in to use this feature</div>
    <?php
} else {

// List elgible
    $eligible = $resource->getIrElgible();
    ?>

    <div class="card px-0 m-2 float-left" style="width: 30em;">
        <div class="card-header font-weight-bold text-center">IR Eligible Players</div>
        <div class="card-body">
            <div class="row" style="white-space: nowrap">
                <div class="col-3 font-weight-bold">Player</div>
                <div class="col-1 font-weight-bold">Pos</div>
                <div class="col-3 font-weight-bold">Reason</div>
                <div class="col-3 font-weight-bold">Exp Return</div>
                <div class="col-1 font-weight-bold">To IR</div>
            </div>
            <?php
            /** @var IRPlayer $player */
            foreach ($eligible as $player) {
                ?>
                <div class="row" style="white-space: nowrap">
                    <div class="col-3"><?= $player->firstName ?> <?= $player->lastName ?></div>
                    <div class="col-1"><?= $player->pos ?></div>
                    <div class="col-3"> <?= $player->details ?></div>
                    <div class="col-3"> <?= $player->expReturn ?></div>
                    <div class="col-1"><label class="switch">
                            <input type="checkbox" name="irAdd" value="<?= $player->playerid ?>"/><span
                                    class="slider round"></span>
                        </label></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <?php
    $current = $resource->getCurrentIr();
    ?>

    <div class="card px-0 m-2 float-left" style="width: 32em;">
        <div class="card-header font-weight-bold text-center">Current IR Players</div>
        <div class="card-body">
            <div class="row justify-content-around" style="white-space: nowrap">
                <div class="col-3 font-weight-bold">Player</div>
                <div class="col-1 font-weight-bold">Pos</div>
                <div class="col-2 font-weight-bold">Since</div>
                <div class="col-3 font-weight-bold">Exp Return</div>
                <div class="col-2 font-weight-bold">Off IR</div>
            </div>
            <?php
            /** @var IRPlayer $player */
            foreach ($current as $player) {
                ?>
                <div class="row justify-content-around" style="white-space: nowrap;">
                    <div class="col-3"><?= $player->firstName ?> <?= $player->lastName ?></div>
                    <div class="col-1"><?= $player->pos ?></div>
                    <div class="col-2"> <?= $player->status ?></div>
                    <div class="col-3"> <?= $player->expReturn ?></div>
                    <div class="col-2"><label class="switch">
                            <input type="checkbox" name="irRemove" value="<?= $player->playerid ?>"
                                   checked="true"/><span
                                    class="slider round"></span>
                        </label></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="row col justify-content-center">
        <button class="btn btn-wmffl" onclick="sub();">Submit</button>
    </div>

    <?php
}
include "base/footer.html";
