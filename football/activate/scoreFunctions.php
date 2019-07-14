<?php
/**
 * Created by IntelliJ IDEA.
 * User: Josh
 * Date: 11/3/2018
 * Time: 5:09 PM
 *
 */

/**
 * @param $thisTeamID
 * @param $thisWeek
 * @param $thisSeason
 * @param $conn
 * @return array
 */
function getOtherTeam($thisTeamID, $thisWeek, $thisSeason, $conn)
{
    $getTeamSQL = "SELECT if(s.scorea >= s.scoreb, s.teamA, s.teamB) as 'teamA', ";
    $getTeamSQL .= "if(s.scorea >= s.scoreb, s.teamB, s.teamA) as 'teamB', ";
    $getTeamSQL .= "if(s.scorea >= s.scoreb, tna.name, tnb.name), ";
    $getTeamSQL .= "if(s.scorea >= s.scoreb, tnb.name, tna.name) ";
    $getTeamSQL .= "FROM schedule s, team ta, team tb, teamnames tna, teamnames tnb ";
    $getTeamSQL .= "WHERE s.Week=$thisWeek AND s.Season=$thisSeason ";
    $getTeamSQL .= "AND (s.TeamA=$thisTeamID OR s.TeamB=$thisTeamID) ";
    $getTeamSQL .= "AND s.teamA=ta.teamid and s.teamb=tb.teamid ";
    $getTeamSQL .= "AND ta.teamid=tna.teamid AND tb.teamid=tnb.teamid ";
    $getTeamSQL .= "AND tna.season=s.Season AND tnb.season=s.season ";

    $results = mysqli_query($conn, $getTeamSQL);
    $row = mysqli_fetch_array($results);
    return $row;
}

function getOtherGames($thisTeamID, $thisWeek, $thisSeason, $conn)
{
    $getTeamSQL = "SELECT s.teamA, s.teamB, ta.abbrev as 'aname', tb.abbrev as 'bname', ";
    $getTeamSQL .= "s.scorea, s.scoreb, s.overtime ";
    $getTeamSQL .= "FROM schedule s, teamnames ta, teamnames tb ";
    $getTeamSQL .= "WHERE s.Week=$thisWeek AND s.Season=$thisSeason ";
//    $getTeamSQL .= "AND s.TeamA<>$thisTeamID and s.TeamB<>$thisTeamID ";
    $getTeamSQL .= "AND s.teama=ta.teamid and s.teamb=tb.teamid ";
    $getTeamSQL .= "AND ta.season=s.season AND tb.season=s.season ";
    $results = mysqli_query($conn, $getTeamSQL) or die("Database error: " . mysqli_error($conn));
    return $results;
//    $row = mysqli_fetch_array($results);
//    return $row;
}


function generateReserves($thisTeamID, $currentSeason, $currentWeek)
{
    $select = <<<EOD
select p.pos, p.lastname, p.firstname, nr.nflteamid as 'team', CONVERT_TZ(n.kickoff, 'SYSTEM', 'GMT') as 'kickoff', n.secRemain, n.complete, p.flmid, s.*, 
if ((r.dateon is null and p.pos<>'HC') or (p.playerid=2637 and wm.season=2018 and wm.week=14), 1, 0) as 'illegal', a.pos as 'startPos', a.teamid as 'teamcheck1', 
r.teamid as 'teamcheck2', n.secRemain, gp1.side as 'GPMe', gp2.side as 'GPThem', CONVERT_TZ(wm.ActivationDue, 'SYSTEM', 'GMT') as 'ActivationDue'
from newplayers p
JOIN weekmap wm
LEFT JOIN roster r on p.playerid=r.playerid and r.dateon<wm.activationDue and (r.dateoff is null or r.dateoff >= wm.activationDue)
LEFT JOIN revisedactivations a on a.season=wm.season and a.week=wm.week and a.playerid=p.playerid
left join stats s on s.season=wm.season and s.week=wm.week and s.statid=p.flmid
left join nflrosters nr on p.playerid=nr.playerid and nr.dateon <= wm.activationdue and (nr.dateoff is null or nr.dateoff >= wm.activationdue)
left join nflgames n on n.week=wm.week and n.season=wm.season and nr.nflteamid in (n.hometeam, n.roadteam)
  LEFT JOIN gameplan gp1 ON wm.season=gp1.season and wm.week=gp1.week and p.playerid=gp1.playerid and gp1.side='Me'
  LEFT JOIN gameplan gp2 ON wm.season=gp2.season and wm.week=gp2.week and p.playerid=gp2.playerid and gp2.side='Them'
WHERE wm.season=$currentSeason and wm.week=$currentWeek and (r.teamid=$thisTeamID or a.teamid=$thisTeamID)
order by p.pos, p.lastname, p.firstname
EOD;
    return $select;
}


function printPlayer($row, $color, $score, $reserveClass = "")
{
    $printString = "<tr><td class=\"c1 c1$color\"><div class=\"posprefix px-1\">{$row['pos']}</div>";
    $printString .= "<div class=\"PQDO\"> </div>";
    $printString .= "<div class=\"lnx\"><a class=\"player\" onmouseover=\"Q('{$row['flmid']}')\">{$row['lastname']}, {$row['firstname']}</a></div>";
    $printString .= "<div class=\"rightLine\">";
    /*
    if (time() > strtotime($row["ActivationDue"])) {
        if ($row["GPMe"] == "Me") {
            $printString .= "<div class=\"gp\">GP+</div> ";
        }
        if ($row["GPThem"] == "Them") {
            $printString .= "<div class=\"gp\">GP-</div> ";
        }
    }
    */
    $printString .= "<div class=\"teamprefix\">{$row['team']}</div></div></td>";
    $printString .= "<td class=\"c2 c2$color\">$score</td></tr>";
    return $printString;
}


function playerJavaScript($row, $score)
{
    $javascriptLine = "player.M{$row['flmid']} = new ply(\"{$row['flmid']}\", \"$score\", \"{$row['secRemain']}\",";
    $javascriptLine .= "\"{$row['firstname']} {$row['lastname']}\", \"{$row['pos']}\", \"{$row['team']}\", ";
    $scoreString = scoreString($row, $row['pos']);
    $javascriptLine .= "\"$scoreString\");\n";
    return $javascriptLine;
}
