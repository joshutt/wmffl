<?
$playerid = 5449;

require_once "utils/start.php";
include_once "DataObjects/Newplayers.php";

$player = new DataObjects_Newplayers;
$player->playerid = $playerid;
$player->find(true);

print "<img src=\"http://images.nfl.com/images/players/60x80/{$player->nflid}.jpg\"><br/>";
print "{$player->firstname} {$player->lastname}";

?>
