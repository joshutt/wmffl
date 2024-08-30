<?php
/**
 * @var $conn mysqli
 * @var $currentSeason int
 */
// establish connection
require 'utils/start.php';

$query = <<<EOD
select tn.teamid, tn.season, tn.name, tn.abbrev, tn.divisionId, t.logo, t.fulllogo
from teamnames tn
join team t on tn.teamid=t.teamid
where tn.season=$currentSeason;
EOD;

$divisionQuery =  "select DivisionID, Name from division d where d.endYear >= $currentSeason";

// Get the Divisions
$results = mysqli_query($conn, $divisionQuery);
$divArray = array();
while (list($divId, $divName) = mysqli_fetch_row($results)) {
   $divArray[$divId] = $divName;
}
mysqli_free_result($results);

// Get All the teams
$results = mysqli_query($conn, $query);
$teamArray = array();
while ($aTeam = mysqli_fetch_object($results)) {
    $teamArray[$aTeam->teamid] = $aTeam;
}

?>

<h2>Teams <?= $currentSeason ?></h2>

<form action="processUpdateTeam.php" method="POST">
<table>

    <tr><th>Name</th><th>Abbreviation</th><th>Logo</th><th>Division</th></tr>
<?php
foreach ($teamArray as $id => $team) {
    ?>

    <tr>
        <td style="padding-right: 10px"><input type="text" name="name-<?=$id?>" size="50" value="<?= $team->name ?>"/></td>
        <td><input type="text" name="abbv-<?=$id?>" size="4" value="<?= $team->abbrev ?>"/></td>
        <td style="padding-right: 10px"><input type="text" name="logo-<?=$id?>" size="20" value="<?= $team->logo ?>"/>
            <input type="checkbox" name="full-<?=$id?>" <?= $team->fulllogo ? 'checked' : ''; ?>/>
        </td>
        <td>
            <select name="division-<?=$id?>">
                <?php
                foreach ($divArray as $id => $name) {
                    $selected = '';
                    if ($team->divisionId == $id) {
                        $selected = 'selected';
                    }
                    print "<option value=\"$id\" $selected>$name</option>";
                }
                ?>
            </select>
        </td>
    </tr>

<?php
}
?>
</table>

    <input type="submit"/>

</form>