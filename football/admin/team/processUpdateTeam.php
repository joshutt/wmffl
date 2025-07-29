<?php
/**
 * @var $conn mysqli
 * @var $currentSeason int
 */
// establish connection
include '../check.inc.php';
require 'base/conn.php';

$query = <<<EOD
select tn.teamid, tn.season, tn.name, tn.abbrev, tn.divisionId, t.logo, t.fulllogo
from teamnames tn
join team t on tn.teamid=t.teamid
where tn.season=$currentSeason;
EOD;

//print_r($_POST);

$results = mysqli_query($conn, $query);
$updates = array();
while ($team = mysqli_fetch_object($results)) {
    $id = $team->teamid;
    if ($_POST['name-'.$id] != $team->name) {
        $newName= $conn->real_escape_string($_POST["name-$id"]);
        $updates[] = "UPDATE teamnames SET name='$newName' WHERE season=$currentSeason and teamid=$id";
        $updates[] = "UPDATE team SET name='$newName' where teamid=$id";
        print "Name changed $id <br/>";
    }

    if ($_POST['abbv-'.$id] != $team->abbrev) {
        $updates[] = "UPDATE teamnames SET abbrev='{$_POST["abbv-$id"]}' WHERE season=$currentSeason and teamid=$id";
        $updates[] = "UPDATE team SET abbrev='{$_POST["abbv-$id"]}' where teamid=$id";
        print "Abbreviation changed $id <br/>";
    }

    if ($_POST['division-'.$id] != $team->divisionId) {
        $updates[] = "UPDATE teamnames SET divisionId='{$_POST["division-$id"]}' WHERE season=$currentSeason and teamid=$id";
        $updates[] = "UPDATE team SET divisionId='{$_POST["division-$id"]}' WHERE teamid=$id";
        print "Division changed $id <br/>";
    }

    if ($_POST['logo-'.$id] != $team->logo) {
        $newLogo = $conn->real_escape_string($_POST["logo-$id"]);
        $updates[] = "UPDATE team SET logo='$newLogo' where teamid=$id";
        print "Logo changed $id <br/>";
    }

    if ($team->fulllogo == 1) {
        if (!array_key_exists("full-$id", $_POST)) {
            $updates[] = "UPDATE team SET fulllogo=0 where teamid=$id";
            print "Full Logo changed $id <br/>";
        }
    } else {
        if (array_key_exists("full-$id", $_POST)) {
            $updates[] = "UPDATE team SET fulllogo=1 where teamid=$id";
            print "Full Logo changed $id <br/>";
        }
    }

}

print '<pre>';
print_r($updates);
print '</pre>';

$counter = 0;
foreach ($updates as $update) {
    mysqli_real_query($conn, $update);
    $counter++;
}
?>

<p><?= $counter ?> successful updates</p>
<p><a href="updateTeamInfo.php">Back</a></p>
<p><a href="/admin">Return to Admin Page</a></p>