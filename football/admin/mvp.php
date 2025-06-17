<?
require_once "utils/start.php";

function singleCompare($scoreA, $scoreB) {
    if ($scoreA < 0) return $scoreA;
    if ($scoreA <= $scoreB) return 0;
    if ($scoreB < 0) return $scoreA;
    return $scoreA-$scoreB;
}

function doubleCompare($scoreA, $scoreB1, $scoreB2) {
    if ($scoreB1 < 0) $scoreB1=0;
    if ($scoreB2 < 0) $scoreB2=0;
    return singleCompare($scoreA, ($scoreB1+$scoreB2)/2);
}

if ($_REQUEST["week"] != null) {
    $thisWeek = $_REQUEST["week"];
} else {
    $thisWeek = $currentWeek-1;
}


$playerSQL = <<<EOD
SELECT a.playerid, a.pos, a.teamid, if(a.teamid=s.teama, s.teamb, s.teama) as 'opp', ps.active, CONCAT(p.firstname, ' ', p.lastname) as 'name',
a.week
FROM revisedactivations a
JOIN playerscores ps ON a.week=ps.week AND a.season=ps.season AND a.playerid=ps.playerid
JOIN schedule s ON s.season=a.season AND s.week=a.week AND a.teamid in (s.teama, s.teamb)
JOIN newplayers p ON p.playerid=a.playerid
WHERE a.season=$currentSeason AND a.week<=$thisWeek AND a.week<=14
ORDER BY s.gameid, a.pos, a.teamid
EOD;


$posVal = array();
$posCount = array();
$allPlayers = array();
$fullPlayers = array();
$results = mysqli_query($conn, $playerSQL) or die("Can't query database: " . mysqli_error($conn));
while ($rows = mysqli_fetch_assoc($results)) {
    $pos = $rows["pos"];
    $teamid = $rows["teamid"];
    $week = $rows["week"];
    if ($rows["active"] >= 0) {
        $posVal[$pos][$teamid][$week] += $rows["active"];
    }
    $posCount[$pos][$teamid][$week] += 1;
    #array_push($allPlayers, $rows);
    $allPlayers[$rows["playerid"]][$week] = $rows;
    $fullPlayers[$rows["playerid"]] = $rows;
}


$finals = array();
foreach ($allPlayers as $playList) {
  foreach ($playList as $player) {
    /*
    print_r($player);
    print "<br/>";
    */

    $pos = $player["pos"];
    $team = $player["teamid"];
    $opp = $player["opp"];
    $numTeam = $posCount[$pos][$team];
    $numOpp = $posCount[$pos][$opp];

    if ($pos == "HC" || $pos=="QB" || $pos=="K" || $pos=="OL") {
        $val = $player["active"] - $posVal[$pos][$opp];
    } else if ($pos=="DL" || $pos=="LB" || $pos=="DB") {
        $val = $player["active"] - $posVal[$pos][$opp] / 2;
    } else if ($numTeam == $numOpp) {
        $val = $player["active"] - $posVal[$pos][$opp] / $numTeam;
    } else {
        if ($pos == "RB") {
            if ($posCount["RB"][$team] + $posCount["TE"][$team] == $posCount["RB"][$opp] + $posCount["TE"][$opp]) {
               $val = $player["active"] - ($posVal["RB"][$opp] + $pos["TE"][$opp]) / ($posCount["RB"][$opp] + $posCount["TE"][$opp]);
            } else {
               $val = $player["active"] - ($posVal["RB"][$opp] + $pos["WR"][$opp]) / ($posCount["RB"][$opp] + $posCount["WR"][$opp]);
            }
        } else if ($pos == "WR") {
            if ($posCount["WR"][$team] + $posCount["TE"][$team] == $posCount["WR"][$opp] + $posCount["TE"][$opp]) {
               $val = $player["active"] - ($posVal["WR"][$opp] + $pos["TE"][$opp]) / ($posCount["WR"][$opp] + $posCount["TE"][$opp]);
            } else {
               $val = $player["active"] - ($posVal["WR"][$opp] + $pos["WR"][$opp]) / ($posCount["WR"][$opp] + $posCount["WR"][$opp]);
            }
        } else {
            if ($posCount["RB"][$team] + $posCount["TE"][$team] == $posCount["RB"][$opp] + $posCount["TE"][$opp]) {
               $val = $player["active"] - ($posVal["RB"][$opp] + $pos["TE"][$opp]) / ($posCount["RB"][$opp] + $posCount["TE"][$opp]);
            } else if ($posCount["WR"][$team] + $posCount["TE"][$team] == $posCount["WR"][$opp] + $posCount["TE"][$opp]) {
               $val = $player["active"] - ($posVal["WR"][$opp] + $pos["TE"][$opp]) / ($posCount["WR"][$opp] + $posCount["TE"][$opp]);
            } else {
               $val = $player["active"] - $posVale["TE"][$opp];
            }
        }
    }
    $finals[$player["playerid"]] += $val;
  }
}

arsort($finals);

$count = 1;
print "<table>";
print "<tr><th colspan=4>Player of the Week</th></tr>";
foreach ($finals as $playerid=>$score) {
    /*
    $playerInfoSQL = "SELECT concat(firstname, ' ', lastname) as 'name', team, pos FROM newplayers WHERE playerid=$playerid";
    $playerResult = mysqli_query($conn, $playerInfoSQL) or die("Dead: ".mysqli_error($conn));
    $playerInfo = mysqli_fetch_array($playerResult);
    */
    $playerInfo = $fullPlayers[$playerid];
    $pos = $playerInfo['pos'];
    print "<tr><td>$count</td><td>${playerInfo['name']}</td>";
    print "<td>$pos</td><td>$score</td></tr>";
    $count++;
    if ($count > 10) break;
}
print "</table>";



$count = 1;
print "<table>";
print "<tr><th colspan=4>Defensive POW</th></tr>";
foreach ($finals as $playerid=>$score) {
    /*
    $playerInfoSQL = "SELECT concat(firstname, ' ', lastname) as 'name', team, pos FROM newplayers WHERE playerid=$playerid";
    $playerResult = mysqli_query($conn, $playerInfoSQL) or die("Dead: ".mysqli_error($conn));
    $playerInfo = mysqli_fetch_array($playerResult);
    */
    $playerInfo = $fullPlayers[$playerid];
    $pos = $playerInfo['pos'];
    if ($pos=="DB" || $pos=="LB" || $pos=="DL") {
        print "<tr><td>$count</td><td>${playerInfo['name']}</td>";
        print "<td>$pos</td><td>$score</td></tr>";
        $count++;
        if ($count > 10) break;
    }
}
print "</table>";
?>


<p><a href="index.php">Return to Admin Page</a></p>
