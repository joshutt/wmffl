<?
require_once "utils/start.php";

//print_r($_REQUEST);

if ($isin) {

$sql = <<<EOD
    INSERT INTO expansionprotections
    (teamid, playerid, type, protected)
    VALUES
EOD;

$protect = $_REQUEST['pro'];
$pullback = $_REQUEST['pb'];
$alternate = $_REQUEST['alt'];

foreach ($protect as $player) {
    $sql .= " ($teamnum, $player, 'protect', 1),";
}

foreach ($pullback as $player) {
    $sql .= " ($teamnum, $player, 'pullback', 0),";
}

foreach ($alternate as $player) {
    $sql .= " ($teamnum, $player, 'alternate', 0),";
}

$sql = trim($sql, ',');

$deleteSql = "DELETE FROM expansionprotections where teamid=$teamnum";

} 

$title = "Protections Saved";
?>

<? include "base/menu.php"; ?>

<h1 align="center">Protections Saved</h1>
<hr/>


<?
mysql_query($deleteSql) or die ("Unable to clear old protections: ".mysql_error());

mysql_query($sql) or die ("Unable to save your protections: ".mysql_error());

?>


<p>Your protections have been saved.</p>

<p><a href="protectList.php">Return to Protections page</a></p>

<? include "base/footer.html"; ?>
