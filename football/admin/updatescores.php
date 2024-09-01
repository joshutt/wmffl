<?php
/**
 * @var $conn mysqli
 */
require_once '/home/joshutt/football/base/conn.php';
include '/home/joshutt/football/base/useful.php';
include '/home/joshutt/football/base/scoring.php';

function generateSelect($thisTeamID, $currentSeason, $currentWeek): string
{
    return <<<EOD
        SELECT p.pos, p.lastname, p.firstname, r.teamid, g.kickoff, g.secRemain, p.flmid, s.*, 
        if (r.dateon is null and p.pos<>'HC', 1, 0) as 'illegal', gp1.side as 'Me', gp2.side as 'Them', wm.ActivationDue
        FROM newplayers p
        JOIN revisedactivations a ON p.playerid=a.playerid
	JOIN weekmap wm ON a.season=wm.season AND a.week=wm.week
        LEFT JOIN roster r ON p.playerid=r.playerid AND (r.dateoff is null OR r.dateoff >= wm.activationdue) AND r.teamid=a.teamid
        LEFT JOIN nflrosters nr ON nr.playerid=p.playerid AND nr.dateoff is null
        LEFT JOIN nflgames g ON g.season=a.season AND g.week=a.week AND nr.nflteamid in (g.homeTeam, g.roadTeam)
        LEFT JOIN stats s ON s.statid=p.flmid AND s.week=a.week AND s.season=a.season
  LEFT JOIN gameplan gp1 ON wm.season=gp1.season and wm.week=gp1.week and p.playerid=gp1.playerid and gp1.side='Me'
  LEFT JOIN gameplan gp2 ON wm.season=gp2.season and wm.week=gp2.week and p.playerid=gp2.playerid and gp2.side='Them'
        WHERE a.teamid=$thisTeamID AND a.season=$currentSeason AND a.week=$currentWeek
EOD;
}


function determinePoints($teamid, $season, $week, $conn) {
    $statSelect = generateSelect($teamid, $season, $week);
    $results = mysqli_query($conn, $statSelect) or die ('Dead: ' . mysqli_error($conn));

    $totalPoints = 0;
    $offPoints = 0;
    $defPoints = 0;
    $penalty = 0;
    $secRemain = 0;
    while ($row = mysqli_fetch_array($results)) {
        $pts = 0;

        // Determine Number of Points
        if ($row['illegal']==1) {
            $penalty += 2;
        } elseif ($row['kickoff'] == null && $row['pos'] != 'HC') {
            $penalty += 2;
        } else {
            switch ($row['pos']) {
                case 'HC' :
                    $pts = scoreHC($row);
                    $offPoints += $pts;
                    break;
                case 'QB' :
                    $pts = scoreQB($row);
                    $offPoints += $pts;
                    break;
                case 'RB' :
                case 'WR' :
                     $pts = scoreOffense($row);
                     $offPoints += $pts;
                     break;
                case 'TE' :    
                     $pts = scoreTE($row);
                     $offPoints += $pts;
                     break;
                case 'K' :
                    $pts = scoreK($row);
                    $offPoints += $pts;
                    break;
                case 'OL' :
                    $pts = scoreOL($row);
                    $offPoints += $pts;
                     break;
                case 'DL' :
                case 'LB' :
                case 'DB' :
                     $pts = scoreDefense($row);
                     $defPoints += $pts;
                     break;
            }
        }

        //print "${row["firstname"]} ${row["lastname"]} = $pts \n";

        $totalPoints += $pts;
        $secRemain += $row['secRemain'];
        //print "Total Pts: $totalPoints  Offensive: $offPoints   Defense: $defPoints \n";
    }
    //print "$teamid-$totalPoints-$offPoints-$defPoints-$penalty<br>";
    return array($totalPoints, $offPoints, $defPoints, $penalty, $secRemain);
}


function updateScore($teamA, $teamB, $season, $week, $aScore, $bScore, $conn): void
{
    $update = "UPDATE schedule SET scorea=$aScore, scoreb=$bScore ";
    $update .= "WHERE season=$season and week=$week and ";
    $update .= "teama=$teamA and teamb=$teamB";
    mysqli_query($conn, $update);
}


$gameSelect = 'SELECT s.teama, s.teamb, w.season, w.week ';
$gameSelect .= 'FROM schedule s, weekmap w ';
$gameSelect .= 'WHERE s.season=w.season and s.week=w.week ';

if (!empty($week)) {
    $gameSelect .= 'AND w.week=' .$week;
    if (!empty($season)) {
        $gameSelect .= ' AND w.season=' .$season;
    }
//} elseif (date("w") == 2 && date("H") >= 11) {
//    $gameSelect .= "AND w.week=".($currentWeek-1);
//    $gameSelect .= " AND w.season=".$currentSeason;
} else { 
    $gameSelect .= 'and now() between w.startdate and w.enddate ';
}
//print $gameSelect;
$gameResults = mysqli_query($conn, $gameSelect);

while ($gameRow = mysqli_fetch_array($gameResults)) {
    $aPts = determinePoints($gameRow['teama'], $gameRow['season'], $gameRow['week'], $conn);
    $bPts = determinePoints($gameRow['teamb'], $gameRow['season'], $gameRow['week'], $conn);
    $aFinal = $aPts[1] - $bPts[2] - $aPts[3];
    $bFinal = $bPts[1] - $aPts[2] - $bPts[3];
    if ($aFinal < 0) $aFinal=0;
    if ($bFinal < 0) $bFinal=0;

    // If the game is over and both scores are negative make it 1-0
    if ($aFinal <0 && $bFinal < 0 && $aPts[4]==0 && $bPts[4] == 0) {
        if ($aFinal > $bFinal) {
            $aFinal = 1;
        } else if ($bFinal > $aFinal) {
            $bFinal = 1;
        }
    }
    //print $gameRow['week'];
    updateScore($gameRow['teama'], $gameRow['teamb'], $gameRow['season'], $gameRow['week'], $aFinal, $bFinal, $conn);
    // print "Updated ".$gameRow['teama']." vs ".$gameRow['teamb']."\n";
}

