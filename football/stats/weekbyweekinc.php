<?php
/**
 * @var $season int
 * @var $conn mysqli
 */

include_once 'utils/teamList.php';
$teamArray = getTeamList($conn, $season);
?>

<div class="row col justify-content-center">
    <div>
        <select id="display" class="mx-1">
            <option value="team">By Team</option>
            <option value="pos">By Pos</option>
        </select>

        <select id="team" class="mx-1">
            <?php
            foreach ($teamArray as $team) {
                if (!empty($searchTeam) && $searchTeam == $team['id']) {
                    $selected = "selected=\"true\" ";
                } else {
                    $selected = '';
                }
                print "<option value=\"{$team['id']}\" $selected>{$team['name']}</option>";
            }
            ?>
        </select>

        <select id="pos" class="mx-1">
            <?php
            foreach (getPosList() as $pos) {
                if (!empty($searchPos) && $searchPos == $pos) {
                    $selected = "selected=\"true\" ";
                } else {
                    $selected = '';
                }
                print "<option value=\"$pos\" $selected>$pos</option>";
            }
            ?>
        </select>
    </div>
    <div>

        <button class="button mx-1" id="refresh" onClick="refresh(); return false;">Refresh</button>

        <button class="button mx-2" id="csv" onClick="csv()">CSV</button>
        <button class="button mx-2" id="json" onClick="csv('json')">JSON</button>
    </div>
</div>
</div>
