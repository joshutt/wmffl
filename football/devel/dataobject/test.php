<?

ini_set("include_path", "/home/wmffl/pear/lib/php:/home/wmffl/scripts/pear:".ini_get("include_path"));

require_once "PEAR.php";
require_once "DB/DataObject.php";


$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$config = parse_ini_file('/home/wmffl/scripts/pear/myconfig.ini', TRUE);
$options = $config['DB_DataObject'];

DB_DataObject::debugLevel($debug);
/*
$user = DB_DataObject::factory('user');
$user->get(10);
$user->Username = 'fred';
$user->update();

print "DOne";
*/

/*
require_once "DataObjects/Players.php";

$player = new DataObjects_Players;
$player->Position = 'RB';
$player->Status = 'A';

$numRows = $player->find();

while ($player->fetch()) {
    print "$player->FirstName $player->LastName<br/>";
}
*/

require_once "DataObjects/Team.php";
#require_once "DataObjects/Players.php";
#require_once "DataObjects/Roster.php";

$team = new DataObjects_Team;
#$players = new DataObjects_Players;
#$roster = new DataObjects_Roster;

#$team->find(true);
#print_r($team);

$team->Name = "Flatulant Chiuanuas";
print_r($team);
if ($team->N == 0) {
    $team->insert();
} else {
    $team->update();
}
print_r($team);

/*
$roster->whereAdd("DateOff is null");

$roster->joinAdd($players);
$team->joinAdd($roster);
$team->selectAs($roster, 'roster_%s');
$team->selectAs($players, 'player_%s');

$team->orderBy('player_Position, player_LastName');

#$players->joinAdd($roster);
#$team->joinAdd($roster);

$count = $team->find();
print "Count is $count<br/>";
while ($team->fetch()) {

    print "{$team->Name} has  {$team->player_FirstName} {$team->player_LastName} ({$team->player_Position} - {$team->player_NFLTeam})<br>";
}
*/
/*
require_once "DataObjects/Weekmap.php";

$week = new DataObjects_Weekmap;
$week->Season = 2006;
$week->Week = 5;
$week->StartDate = '2006-09-04';
$week->insert();
*/




print "Done";
?>
