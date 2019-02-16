<table>
<?

function printstats($player) {
    $returnString = "";
    
    $pos = $player["position"];

    switch($pos) {
        case 'HC' :
            $returnString .= "{$player["ptdiff"]} Point Differential<br/>";
            break;
        case 'RB' :   case 'WR' :   case 'TE' :
            $returnString .= "{$player["rec"]} Receptions<br/>";
        case 'QB' :
            $returnString .= "{$player["yards"]} Yards<br/>";
            if ($player["tds"] > 0) {
                $returnString .= "{$player["tds"]} Touchdowns<br/>";
            }
            if ($player["specTD"] > 0) {
                $returnString .= "{$player["specTD"]} Special Teams Touchdowns<br/>";
            }
            if ($player["2pt"] > 0) {
                $returnString .= "{$player["2pt"]} 2-pt Conversions<br/>";
            }
            if ($player["fum"] > 0) {
                $returnString .= "{$player["fum"]} Fumbles<br/>";
            }
            if ($player["intthrow"] > 0) {
                $returnString .= "{$player["intthrow"]} Interceptions<br/>";
            }
            break;
        case 'K' :
            if ($player["XP"] > 0) {
                $returnString .= "{$player["XP"]} Extra Points<br/>";
            }
            if ($player["MissXP"] > 0) {
                $returnString .= "{$player["MissXP"]} Missed Extra Points<br/>";
            }
            if ($player["FG30"] > 0) {
                $returnString .= "{$player["FG30"]} Field Goals (0-39 yards)<br/>";
            }
            if ($player["FG40"] > 0) {
                $returnString .= "{$player["FG40"]} Field Goals (40-49 yards)<br/>";
            }
            if ($player["FG50"] > 0) {
                $returnString .= "{$player["FG50"]} Field Goals (50-59 yards)<br/>";
            }
            if ($player["FG60"] > 0) {
                $returnString .= "{$player["FG60"]} Field Goals (60+ yards)<br/>";
            }
            if ($player["MissFG30"] > 0) {
                $returnString .= "{$player["MissFG30"]} Missed Field Goals (0-30 yards)<br/>";
            }
            if ($player["specTD"] > 0) {
                $returnString .= "{$player["specTD"]} Special Team Touchdowns<br/>";
            }
            if ($player["2pt"] > 0) {
                $returnString .= "{$player["2pt"]} 2-pt Conversions<br/>";
            }
            break;
        case 'OL' :
            $returnString .= "{$player["yards"]} Rushing Yards<br/>";
            $returnString .= "{$player["sacks"]} Sacks Allowed<br/>";
            if ($player["tds"] > 0) {
                $returnString .= "{$player["tds"]} Rushing Touchdowns<br/>";
            }
            break;
        case 'DL' :   case 'LB' :   case 'DB' :
            if ($player["tackles"] > 0) {
                $returnString .= "{$player["tackles"]} Tackles<br/>";
            }
            if ($player["passdefend"] > 0) {
                $returnString .= "{$player["passdefend"]} Pass Defenses<br/>";
            }
            if ($player["sacks"] > 0) {
                $returnString .= "{$player["sacks"]} Sacks<br/>";
            }
            if ($player["intcatch"] > 0) {
                $returnString .= "{$player["intcatch"]} Interceptions<br/>";
            }
            if ($player["forcefum"] > 0) {
                $returnString .= "{$player["forcefum"]} Forced Fumbles<br/>";
            }
            if ($player["fumrec"] > 0) {
                $returnString .= "{$player["fumrec"]} Fumble Recoveries<br/>";
            }
            if ($player["returnyards"] > 0) {
                $returnString .= "{$player["returnyards"]} Return Yards<br/>";
            }
            if ($player["Safety"] > 0) {
                $returnString .= "{$player["Safety"]} Safeties<br/>";
            }
            if ($player["tds"] > 0) {
                $returnString .= "{$player["tds"]} Touchdowns<br/>";
            }
            if ($player["specTD"] > 0) {
                $returnString .= "{$player["specTD"]} Special Team Touchdowns<br/>";
            }
            if ($player["2pt"] > 0) {
                $returnString .= "{$player["2pt"]} 2-pt Conversions<br/>";
            }
            if ($player["blockpunt"] > 0) {
                $returnString .= "{$player["blockpunt"]} Blocked Punts<br/>";
            }
            if ($player["blockfg"] > 0) {
                $returnString .= "{$player["blockfg"]} Blocked FGs<br/>";
            }
            if ($player["blockxp"] > 0) {
                $returnString .= "{$player["blockxp"]} Blocked XP<br/>";
            }
            break;

    }
    
    return $returnString;
}


$query = <<<EOD
select t.name, p.pos as 'position', p.lastname, p.firstname, nr.nflteamid as 'NFLteam', n.status,
p.flmid as 'statid', s.*, if (r.dateon is null, 1, 0) as 'illegal', ps.pts
from newplayers p
join activations a on p.playerid in (a.HC, a.QB, a.RB1, a.RB2, a.WR1, a.WR2, a.TE, a.K, a.OL, a.DL1, a.DL2, a.LB1, a.LB2, a.DB1, a.DB2)
join weekmap w on w.season=a.season and w.week=a.week
join teamnames t on t.teamid=a.teamid and t.season=w.season
left join nflrosters nr
on nr.dateon <= w.activationdue and (nr.dateoff is null or nr.dateoff >= w.activationdue) and 
nr.playerid=p.playerid
left join stats s
on s.statid=p.flmid and s.week=a.week and s.season=a.season
left join playerscores ps
on ps.playerid=p.playerid and ps.week=s.week and ps.season=s.season
left join roster r on r.playerid=p.playerid and r.teamid=a.teamid
and r.dateon<=w.activationdue
and (r.dateoff is null or r.dateoff > w.activationdue)
left join nflstatus n on nr.nflteamid=n.nflteam and n.week=a.week 
and n.season=a.season
where a.season=$thisSeason and a.week=$thisWeek
order by t.name, p.pos, p.lastname, p.firstname
EOD;

$results = mysql_query($query) or die("Error: ".mysql_error());

$actPlayers = array();
$team = array();
$currentTeam = "";
while ($player = mysql_fetch_array($results)) {
//    print_r($player);
    if ($currentTeam != $player["name"]) {
        //print_r($team);
        $actPlayers[$currentTeam] = $team;
        $team = array();
        $currentTeam = $player["name"];
    }
    array_push($team, $player);
    //print_r($team);
//    if (!array_key_exists($player["name"], $actPlayers)) {
//        $actPlayers[$player["name"]] = array();
//    }
//    array_push($player, &$actPlayers[$player["name"]]);
}
$actPlayers[$currentTeam] = $team;

foreach($gameArray as $game) {
    print "<tr><th colspan=\"9\" align=\"center\">{$game[0]} {$game[1]} - {$game[2]} {$game[3]}</th></tr>";
    $teama = $actPlayers[$game[0]];
    $teamb = $actPlayers[$game[2]];
    $loop = max(sizeof($teama), sizeof($teamb));
    $teamaOff = 0; $teamaDef = 0;
    $teambOff = 0; $teambDef = 0;
    $teamaPen = 0; $teambPen = 0;


    for ($i=0; $i<$loop; $i++) {
        $playera = $teama[$i];
        $playerb = $teamb[$i];

        if ($playera["position"] != "HC" && ($playera["status"] == "B" 
                || $playera["NFLteam"] == "" || $playera["illegal"] == 1)) {
            $teamaPen += -2;
            $playera["pts"] = "X";
        }
        if ($playerb["position"] != "HC" && ($playerb["status"] == "B" 
                || $playerb["NFLteam"] == "" || $playerb["illegal"] == 1)) {
            $teambPen += -2;
            $playerb["pts"] = "X";
        }

        if ($playera["pts"] == "") {$playera["pts"] = 0;}
        if ($playerb["pts"] == "") {$playerb["pts"] = 0;}

        if ($playera["position"] == "DL" || $playera["position"] == "LB" ||
            $playera["position"] == "DB") {
                $teamaDef += $playera["pts"];
        } else {
            $teamaOff += $playera["pts"];
        }
        if ($playerb["position"] == "DL" || $playerb["position"] == "LB" ||
            $playerb["position"] == "DB") {
                $teambDef += $playerb["pts"];
        } else {
            $teambOff += $playerb["pts"];
        }
        
        
        print <<<EOD
<tr bgcolor="#dddddd">
<td>{$playera["position"]}</td>
<td>{$playera["firstname"]} {$playera["lastname"]}</td>
<td>{$playera["NFLteam"]}</td>
<td>{$playera["pts"]}</td>
<td bgcolor="#ffffff" width="20"></td>
<td>{$playerb["position"]}</td>
<td>{$playerb["firstname"]} {$playerb["lastname"]}</td>
<td>{$playerb["NFLteam"]}</td>
<td>{$playerb["pts"]}</td>
</tr>
EOD;

        print "<tr><td></td>";
        print "<td align=\"right\" colspan=\"2\" valign=\"top\"><font size=\"-1\">".printstats($playera)."</font></td>";
        print "<td colspan=\"3\"></td>";
        print "<td align=\"right\" colspan=\"2\" valign=\"top\"><font size=\"-1\">".printstats($playerb)."</font></td></tr>";

    }

    $finalScoreA = $teamaOff - $teambDef + $teamaPen;
    $finalScoreB = $teambOff - $teamaDef + $teambPen;
    if ($finalScoreA < 0) {$finalScoreA = 0;}
    if ($finalScoreB < 0) {$finalScoreB = 0;}
    if ($finalScoreA == 0 && $finalScoreB == 0) {
        if ($teamaOff + $teamaDef +$teamaPen > $teambOff + $teambDef +$teambPen) {
            $finalScoreA = 1;
        } else if ($teamaOff + $teamaDef + $teamaPen < $teambOff + $teambDef + $teambPen) {
            $finalScoreB = 1;
        }
    }

    print <<<EOD
<tr bgcolor="#dddddd">
<td colspan="3" align="right">Offensive Score</td><td>$teamaOff</td>
<td bgcolor="#ffffff" width="20"></td>
<td colspan="3" align="right">Offensive Score</td><td>$teambOff</td>
</tr>
<tr bgcolor="#dddddd">
<td colspan="3" align="right">Defensive Score</td><td>$teamaDef</td>
<td bgcolor="#ffffff" width="20"></td>
<td colspan="3" align="right">Defensive Score</td><td>$teambDef</td>
</tr>
EOD;

    if ($teamaPen != 0 || $teambPen != 0) {
        print <<<EOD
<tr bgcolor="#dddddd">
<td colspan="3" align="right">Penalties</td><td>$teamaPen</td>
<td bgcolor="#ffffff" width="20"></td>
<td colspan="3" align="right">Penalties</td><td>$teambPen</td>
</tr>
EOD;
    }

    print <<<EOD
<tr bgcolor="#dddddd">
<td colspan="3" align="right">Final Score</td><td>$finalScoreA</td>
<td bgcolor="#ffffff" width="20"></td>
<td colspan="3" align="right">Final Score</td><td>$finalScoreB</td>
</tr>

<tr><td height="20"></td></tr>

EOD;
    
    print "<tr height=\"10\"></tr>";
}

?>
</table>
