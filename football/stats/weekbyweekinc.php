<?php 
include_once "utils/teamList.php";
$teamArray = getTeamList($season);
?>

<div class="selectOptions">
	<select id="display">
	    <option value="team">By Team</option>
	    <option value="pos">By Pos</option>
	</select>

	<select id="team">
<?php
foreach ($teamArray as $team) {
    if ($searchTeam == $team["id"]) { 
	$selected = "selected=\"true\" "; 
    } else {
	$selected = "";
    }
    print "<option value=\"{$team["id"]}\" $selected>${team["name"]}</option>";
}
?>
	</select>

	<select id="pos">
<?php
foreach (getPosList() as $pos) {
    if ($searchPos == $pos) {
	$selected = "selected=\"true\" ";
    } else {
	$selected = "";
    }
    print "<option value=\"$pos\" $selected>$pos</option>";
}
?>
	</select>

	<button class="button" id="refresh" onClick="refresh(); return false;">Refresh</button>

</div>

<div class="formatOptions">
	<button class="button" id="csv" onClick="csv()">CSV</button>
	<button class="button" id="json" onClick="csv('json')">JSON</button>
</div>
<div class="tableOption">
