<?
require_once "$DOCUMENT_ROOT/utils/start.php";

//print_r($_REQUEST);

if ($isin) {

$sql = <<<EOD
    INSERT INTO expansionprotections
    (teamid, playerid, type)
    VALUES
EOD;

$protect = $_REQUEST['pro'];
$pullback = $_REQUEST['pb'];
$alternate = $_REQUEST['alt'];

foreach ($protect as $player) {
    $sql .= " ($teamnum, $player, 'protect'),";
}

foreach ($pullback as $player) {
    $sql .= " ($teamnum, $player, 'pullback'),";
}

foreach ($alternate as $player) {
    $sql .= " ($teamnum, $player, 'alternate'),";
}

$sql = trim($sql, ',');

$deleteSql = "DELETE FROM expansionprotections where teamid=$teamnum";

} 

$title = "Protections Saved";
?>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<h1 align="center">Protections Saved</h1>
<hr/>


<?
mysql_query($deleteSql) or die ("Unable to clear old protections: ".mysql_error());

mysql_query($sql) or die ("Unable to save your protections: ".mysql_error());

?>


<p>Your protections have been saved.</p>

<p><a href="protectList.php">Return to Protections page</a></p>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
