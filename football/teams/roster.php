<?php
include 'utils/reportUtils.php';
include 'utils/injuryUtils.php';

$thequery = "select DATE_FORMAT(greatest(max(r.DateOn),max(r.DateOff)), '%M %e, %Y'), tp.TransPts+tp.ProtectionPts, tp.TotalPts, count(r.dateon)-count(r.dateoff)-1 from roster r, team t, transpoints tp where r.teamid=t.teamid and t.teamid=tp.teamid and t.teamid=$viewteam and tp.season=$currentSeason group by t.teamid";
$result = mysqli_query($conn, $thequery);
$theDate = mysqli_fetch_row($result);


?>

<div class="roster d-flex justify-content-center row">
    <div class="row text-center">
        <div class="col-12 font-weight-bold h4">Current Roster</div>
        <div class="col-6"><?php
            $ptsLeft = $theDate[2] - $theDate[1];
            if ($ptsLeft > 0) {
                print "$ptsLeft Remaining Free Transactions";
            } else {
                print abs($ptsLeft) . ' extra transactions used';
            }
            ?>
        </div>
        <div class="col-6"><?= $theDate[3] ?> players on roster</div>
    </div>
    <div class="col-12 d-flex justify-content-center">
        <?php
        //$teamname = $_POST["teamname"];
        //	print "Team name: ".$teamname;
        //$thequery = "select p.lastname, p.pos, p.team, IF(p.firstname <> '', concat(', ',p.firstname), '') from newplayers p, roster r, team t where p.playerid=r.playerid and r.teamid=t.teamid and r.dateoff is null and t.teamid=$viewteam order by p.pos, p.lastname";
        $thequery = "select p.lastname, p.pos, p.team, b.week,
       IF(p.firstname <> '', concat(', ',p.firstname), '') as 'firstname',
       r.DateOn, i.status as 'injury', max(pocos.cost) as 'cost', p.dob, TIMESTAMPDIFF(YEAR, p.dob, now()) as 'age',
       ifnull(ps.pts,0) as 'pts', ir.current as 'ir'
from newplayers p
       join roster r on p.playerid=r.playerid and r.dateoff is null
       join team t on r.teamid=t.teamid
       join weekmap wm on wm.StartDate <= now() and wm.EndDate >= now()
       left join nflbyes b on p.team=b.nflteam and b.season=wm.season
       left join newinjuries i on i.playerid=p.playerid and i.season=wm.season and i.week=wm.week
       left join ir on p.playerid=ir.playerid and ir.dateoff is null
       left join protectioncost pc on p.playerid=pc.playerid and pc.season=if(wm.week <= 1, wm.season, wm.season+1)    
       join positioncost pocos on p.pos=pocos.position and pocos.endSeason is null and pocos.years <= ifnull(pc.years, 0)
       left join (
                 select playerid, season, sum(pts) as 'pts'
                 from playerscores
                 group by playerid, season
                 ) ps on p.playerid=ps.playerid and ps.season=if(wm.week <= 1, wm.season-1, wm.season)
where t.teamid=$viewteam
group by p.playerid
order by p.pos, p.lastname";

        $result = mysqli_query($conn, $thequery) or die(mysqli_error($conn));
        $hold = array();
        while ($player = mysqli_fetch_array($result)) {
            $date = date_create($player['DateOn']);
            $inj = shortenInjury($player['injury']);
            if ($player['ir'] == 1) {
                $inj = 'IR';
            }
            $newItem = array('pos' => $player['pos'], 'name' => $player['lastname'] . $player['firstname'],
                'team' => $player['team'], 'bye' => $player['week'], 'age' => $player['age'], 'injury' => $inj,
                'date' => date_format($date, 'M j, Y'), 'cost' => $player['cost'],
                'pts' => $player['pts']);
            array_push($hold, $newItem);
        }
        mysqli_close($conn);

        $proSeason = $currentSeason + 1;
        $ptsSeason = $currentSeason;
        if ($currentWeek <= 1) {
            $proSeason = $currentSeason;
            $ptsSeason = $currentSeason - 1;
        }
        $titles = array('Pos', 'Name', 'Team', 'Bye<br/>Week', 'Age', 'Injury', 'Aquired', "$proSeason<br/>Cost", "$ptsSeason<br/>Pts");
        outputHtml($titles, $hold);
        ?>
    </div>
</div>

