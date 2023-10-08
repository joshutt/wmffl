<?php
require_once "utils/start.php";

$sql = <<<EOD

SELECT p.firstname, p.lastname, p.pos, ps.season, ps.week, ps.active, r.TeamID, nr.nflteamid, tn.name as 'teamname', tn.abbrev
FROM newplayers p
JOIN playerscores ps ON p.playerid=ps.playerid
JOIN weekmap wm ON ps.season=wm.Season and ps.week=wm.Week
LEFT JOIN roster r on p.playerid=r.PlayerID and r.DateOn<wm.ActivationDue
and (r.DateOff > wm.ActivationDue or r.DateOff is null)
LEFT JOIN teamnames tn ON tn.season=wm.Season and tn.teamid=r.TeamID
LEFT JOIN nflrosters nr on p.playerid=nr.playerid and nr.dateon <= wm.ActivationDue
and (nr.dateoff is null or nr.dateoff >= wm.ActivationDue)
WHERE ps.active is not null AND p.pos='%s'
ORDER BY p.pos, ps.active DESC, wm.season, wm.week DESC
LIMIT 30;
EOD;

//print $sql;

function printPlayer($player, $count, $hlSeason) {
    if ($player['season'] == $hlSeason) {
        $class = "currentSeason";
    } else {
        $class = "oSeason";
    }

    print "<tr class=\"$class\"><td>$count</td><td>{$player['name']}</td><td>{$player['nfl']}</td>";
    print "<td>{$player['team']}</td><td>{$player['season']}-{$player['week']}</td>";
    print "<td>{$player['pts']}</td></tr>";
}



function printRankList($sql, $pos, $hlSeason=2016, $extraList = array()) {
    global $conn;
    $result = mysqli_query($conn, sprintf($sql, $pos)) or die("Unable to run query: " . mysqli_error($conn));

    $posArray = array();
    while ($players = mysqli_fetch_array($result)) {
        $name = $players['firstname']." ".$players['lastname'];
        $season = $players['season'];
        $week = $players['week'];
        $pts = $players['active'];
        $nflTeam = $players['nflteamid'];
        $team = $players['abbrev'];
        $player = array('name' => $name, 'season' => $season, 'week' => $week, 'pts' => $pts, 'nfl'=>$nflTeam, 'team'=>$team);
        array_push($posArray, $player);
    }
    ?>


    <table>
    <tr><th colspan="6"><?= $pos ?></th></tr>
    <tr><th>#</th><th>Name</th><th>NFL</th><th>Team</th><th>Week</th><th>Pts</th></tr>
<?php
    $count = 0;
    $limitScore = 0;
    $extraCount = 0;
    foreach ($posArray as $player) {
        $count++;
        if ($count == 10) {
            $limitScore = $player['pts'];
        } else if ($count > 10 && $player['pts'] < $limitScore) {
            break; 
        }

        while (isset($extraList[$extraCount]) && $extraList[$extraCount]["pts"] >= $player["pts"]) {
            printPlayer($extraList[$extraCount], $count, $hlSeason);
            $extraCount++;
            $count++;

            if ($count >= 10) {
                $limitScore = $extraList[$extraCount-1]["pts"];
                if ($player['pts'] < $limitScore) {
                    break;
                }
            }
        }

        printPlayer($player, $count, $hlSeason);

    }
    ?>
    </table>
<?php
}

$qbList = array();
$rbList = array(array('name' => 'Mike Anderson', 'season' => 2000, "week" => 14, "pts" => 41, "nfl"=>"DEN", "team"=>"MM"));
$wrList = array(array('name' => 'Jimmy Smith', 'season' => 2000, "week" => 2, "pts" => 52, "nfl"=>"JAC", "team"=>"ZEN"),
               array("name" => "Jerry Rice", "season" => 1994, "week" => 12, "pts" => 40, "nfl"=>"SF", "team"=>"NOR"),
               array("name" => "Sterling Sharpe", "season" => 1993, "week" => 8, "pts" => 39, "nfl"=>"GB", "team"=>"SLA"),
               array("name" => "Jerry Rice", "season" => 1993, "week" => 11, "pts" => 39, "nfl"=>"SF", "team"=>"BAR"));
$teList = array(array("name" => "Shannon Sharpe", "season" => 1996, "week" => 6, "pts"=>36, "nfl"=>"DEN", "team"=>"BAR"),
               array("name" => "Ben Coates", "season" => 1994, "week" => 1, "pts" => 26, "nfl"=>"NE", "team"=>"WAR"));
$kList = array(array("name"=>"Gary Anderson", "season"=>1998, "week"=>15, "pts"=>23, "nfl"=>"MIN", "team"=>"WER"),
              array("name"=>"Jeff Wilkins", "season"=>2000, "week"=>5, "pts"=>23, "nfl"=>"STL", "team"=>"NOR"));
$olList = array(array("name"=>"St. Louis Rams", "season"=>2001, "week"=>9, "pts"=>33, "nfl"=>"STL", "team"=>"HEM"),
                array("name"=>"San Francisco 49ers", "season"=>1998, "week"=>15, "pts"=>29, "nfl"=>"SF", "team"=>"WER"));
$dlList = array(array("name"=>"Tony Brackens", "season"=>1999, "week"=>12, "pts"=>26, "nfl"=>"JAC", "team"=>"CRU"),
                array("name"=>"Michael Strahan", "season"=>1998, "week"=>1, "pts"=>23, "nfl"=>"NYG", "team"=>"WER"),
                array("name"=>"Chris Doleman", "season"=>1996, "week"=>12, "pts"=>22, "nfl"=>"SF", "team"=>"FS"));
$lbList = array(array("name"=>"Ken Norton", "season"=>1995, "week"=>8, "pts"=>44, "nfl"=>"SF", "team"=>"IRA"),
                array("name"=>"Brian Urlacher", "season"=>2001, "week"=>4, "pts"=>30, "nfl"=>"CHI", "team"=>"HEM"),
                array("name"=>"Donnie Edwards", "season"=>1999, "week"=>15, "pts"=>29, "nfl"=>"KC", "team"=>"BAR"));
$dbList = array(array("name"=>"Darren Woodson", "season"=>1995, "week"=>5, "pts"=>29, "nfl"=>"DAL", "team"=>"IRA"),
                array("name"=>"Ronde Barber", "season"=>2001, "week"=>15, "pts"=>28, "nfl"=>"TB", "team"=>"ZEN"));


$cssList = array("/base/css/history.css");
$title = "Single Game Records";
include "base/menu.php";
?>

<h1 class="full"><?= $title ?></h1>

<div class="posList"><? printRankList($sql, "QB", $currentSeason, $qbList); ?></div>
<div class="posList"><? printRankList($sql, "RB", $currentSeason, $rbList); ?></div>
<div class="posList"><? printRankList($sql, "WR", $currentSeason, $wrList); ?></div>
<div class="posList"><? printRankList($sql, "TE", $currentSeason, $teList); ?></div>
<div class="posList"><? printRankList($sql, "K", $currentSeason, $kList); ?></div>
<div class="posList"><? printRankList($sql, "OL", $currentSeason, $olList); ?></div>
<div class="posList"><? printRankList($sql, "DL", $currentSeason, $dlList); ?></div>
<div class="posList"><? printRankList($sql, "LB", $currentSeason, $lbList); ?></div>
<div class="posList"><? printRankList($sql, "DB", $currentSeason, $dbList); ?></div>

<?php include 'base/footer.php'; ?>
