<?
require_once "setup.php";

include_once "DataObjects/Team.php";
include_once "DataObjects/Roster.php";
include_once "DataObjects/Players.php";

$team = new DataObjects_Team;
$team->active = 1;
$team->find();

print "<form>";
print "<select name=\"teamid\" onChange=\"submit();\">";
print "<option></option>";
while($team->fetch()) {
    print "<option value=\"{$team->TeamID}\">{$team->Name}</option>";
}
print "</select>";
print "</form>";


if ($teamid != "") {
    $roster = new DataObjects_Roster;
    $players = new DataObjects_Players;
    
    $roster->TeamID = $teamid;
    $roster->whereAdd("DateOff is null");
    $roster->joinAdd($players);
    $roster->selectAs($players, "players_%s");
    $roster->orderBy("players_Position, players_LastName");

    $roster->find();
    print "<ul>";
    while ($roster->fetch()) {
        print "<li>{$roster->players_FirstName} {$roster->players_LastName}</li>";
    }
    print "</ul>";
}
?>
