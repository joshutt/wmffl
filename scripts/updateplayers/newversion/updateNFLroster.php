<?
require_once "setup.php";

include_once "DataObjects/Newplayers.php";
include_once "processTransactions.php";

$player = new DataObjects_Newplayers;

$db = $player->getDatabaseConnection();
#print_r($db);
$db->autoCommit(false);
$db->query("DELETE FROM nflrosters");

$player->find();
while ($player->fetch()) {
    $rosterArr = applyTransactions($player);
    foreach($rosterArr as $roster) {
        $roster->save();        
    }
}

$db->commit();
?>
