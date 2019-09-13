<?
require_once "setup.php";

include "processPlayers.php";
include "processTransactions.php";


ini_set("max_execution_time", "300");
$player = array();

print "Process Players - ".time()."\n";
$fp = fopen("../data/play2005.nfl", "rb");
processPlayerFile($fp, $player);
fclose($fp);

print "Process Player extend - ".time()."\n";
$fp = fopen("../data/pla!2005.nfl", "rb");
processPlayerFile($fp, $player);
fclose($fp);

#print_r($player);
print "Save Players - ".time()."\n";
savePlayers($player);

$transArr = array();

print "Process Transactions - ".time()."\n";
$fp = fopen("../data/tran2005.nfl", "rb");
processTransFile($fp, $player, $transArr);
fclose($fp);

print "Process Transactions Extend - ".time()."\n";
$fp = fopen("../data/tra!2005.nfl", "rb");
processTransFile($fp, $player, $transArr);
fclose($fp);

print "Save Transactions - ".time()."\n";
saveTransactions($transArr);

#print_r($player);
#print_r($transArr);
print "Players: ".count($player)."\n";
print "Transactions: ".count($transArr)."\n";
print "Time: ".time()."\n";
?>
