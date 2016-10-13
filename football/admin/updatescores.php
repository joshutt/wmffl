<?
#require_once "/home/wmffl/public_html/base/conn.php";
#include "/home/wmffl/public_html/base/useful.php";
#include "/home/wmffl/public_html/base/scoring.php";
require_once "/home/joshutt/football/base/conn.php";
include "/home/joshutt/football/base/useful.php";
include "/home/joshutt/football/base/scoring.php";

function generateSelect($thisTeamID, $currentSeason, $currentWeek) {
    /*
    $select = "select p.position, p.lastname, p.firstname, p.NFLteam, n.status, p.statid, s.* ";
    $select .= "from players p, activations a, nflstatus n left join stats s ";
    $select .= "on s.statid=p.statid and s.week=a.week and s.season=a.season ";
    $select .= "where a.season=".$currentSeason." and a.week=".$currentWeek." and ";
    $select .= "a.teamid=".$thisTeamID." and p.playerid in ";
    $select .= "(a.HC, a.QB, a.RB1, a.RB2, a.WR1, a.WR2, a.TE, a.K, a.OL, a.DL1, ";
    $select .= "a.DL2, a.LB1, a.LB2, a.DB1, a.DB2) ";
    $select .= "and p.NFLTeam=n.NFLTeam and n.week=a.week and n.season=a.season ";
    $select .= "order by p.position, p.lastname, p.firstname ";
    */
    $select = <<<EOD
        SELECT p.pos, p.lastname, p.firstname, r.teamid, g.kickoff, g.secRemain, p.flmid, s.*, if (r.dateon is null and p.pos<>'HC', 1, 0) as 'illegal'
        FROM newplayers p
        JOIN revisedactivations a ON p.playerid=a.playerid
	JOIN weekmap wm ON a.season=wm.season AND a.week=wm.week
        LEFT JOIN roster r ON p.playerid=r.playerid AND (r.dateoff is null OR r.dateoff >= wm.activationdue) AND r.teamid=a.teamid
        LEFT JOIN nflrosters nr ON nr.playerid=p.playerid AND nr.dateoff is null
        LEFT JOIN nflgames g ON g.season=a.season AND g.week=a.week AND nr.nflteamid in (g.homeTeam, g.roadTeam)
        LEFT JOIN stats s ON s.statid=p.flmid AND s.week=a.week AND s.season=a.season
        WHERE a.teamid=$thisTeamID AND a.season=$currentSeason AND a.week=$currentWeek
EOD;
    
    return $select;
}


function determinePoints($teamid, $season, $week, $conn) {
    $statSelect = generateSelect($teamid, $season, $week);
    $results = mysql_query($statSelect, $conn) or die ("Dead: ".mysql_error());

    $totalPoints = 0;
    $offPoints = 0;
    $defPoints = 0;
    $penalty = 0;
    while ($row = mysql_fetch_array($results)) {
        $pts = 0;
        if ($row['illegal']==1) {
            $penalty += 2;
        if ($teamid == 3) {
        //print_r($row);
            //print "<br/>";  
            }
        } elseif ($row['kickoff'] == null && $row['pos'] != 'HC') {
            $penalty += 2;
        if ($teamid == 3) {
        //print_r($row);
            //print "<br/>"; 
           }
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
        $totalPoints += $pts;
        //print $row["firstname"]." ".$row["lastname"]." - ".$pts."\n";
    }
    //print "$teamid-$totalPoints-$offPoints-$defPoints-$penalty<br>";
    return array($totalPoints, $offPoints, $defPoints, $penalty);
}


function updateScore($teamA, $teamB, $season, $week, $aScore, $bScore, $conn) {
    $update = "UPDATE schedule SET scorea=$aScore, scoreb=$bScore ";
    $update .= "WHERE season=$season and week=$week and ";
    $update .= "teama=$teamA and teamb=$teamB";
    mysql_query($update, $conn);
}


$gameSelect = "SELECT s.teama, s.teamb, w.season, w.week ";
$gameSelect .= "FROM schedule s, weekmap w ";
$gameSelect .= "WHERE s.season=w.season and s.week=w.week ";

if ($week != '') {
    $gameSelect .= "AND w.week=".$week;
    if ($season != '') {
        $gameSelect .= " AND w.season=".$season;
    }
} elseif (date("w") == 2 && date("H") >= 11) {
//} elseif (date("w") == 2 && date("H") >= 2) {
    $gameSelect .= "AND w.week=".($currentWeek-1);
    $gameSelect .= " AND w.season=".$currentSeason;
} else { 
    $gameSelect .= "and now() between w.startdate and w.enddate ";
}
//print $gameSelect;
$gameResults = mysql_query($gameSelect, $conn);

while ($gameRow = mysql_fetch_array($gameResults)) {
    $aPts = determinePoints($gameRow['teama'], $gameRow['season'], $gameRow['week'], $conn);
    $bPts = determinePoints($gameRow['teamb'], $gameRow['season'], $gameRow['week'], $conn);
    $aFinal = $aPts[1] - $bPts[2] - $aPts[3];
    $bFinal = $bPts[1] - $aPts[2] - $bPts[3];
    if ($aFinal < 0) $aFinal=0;
    if ($bFinal < 0) $bFinal=0;

    //print $gameRow['week'];
    updateScore($gameRow['teama'], $gameRow['teamb'], $gameRow['season'], $gameRow['week'], $aFinal, $bFinal, $conn);
    print "Updated ".$gameRow['teama']." vs ".$gameRow['teamb']."\n";
}

?>
