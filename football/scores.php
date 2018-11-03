<?
require_once "utils/connect.php";


if ($currentWeek < 1) {
    $thisSeason = $currentSeason - 1;
} else {
    $thisSeason = $currentSeason;
}

$check = "select wm.week, wm.weekname
from weekmap wm
JOIN (select wm.season, max(wm.week) as 'week'
      from weekmap wm
      WHERE (DATE_SUB(wm.DisplayDate, INTERVAL 12 HOUR) < now() or wm.week = 1)
        and wm.season = $thisSeason) maxweek
ON wm.Season=maxweek.season and wm.week=maxweek.week";
$checRe = mysqli_query($conn, $check) or die("Dead: $check<br/>" . mysqli_error($conn));
list($useWeek, $weekname) = mysqli_fetch_row($checRe);


$sql = "SELECT wm.weekname, wm.week, s.teama, if(s.scorea>=s.scoreb,t1.name, t2.name) as 'leadname', 
if(s.scorea>=s.scoreb,s.scorea,s.scoreb) as 'leadscore', if(s.scorea>=s.scoreb,t2.name, t1.name) as 'trailname', 
if(s.scorea>=s.scoreb,s.scoreb,s.scorea) as 'trailscore', s.label, s.overtime  
FROM weekmap wm, schedule s, team t1, team t2
where (DATE_SUB(wm.displaydate, INTERVAL 12 HOUR) < now() OR wm.week=1)
and wm.season=$thisSeason and s.season=wm.season and s.week=wm.week
and s.teama=t1.teamid and s.teamb=t2.teamid
and wm.week=$useWeek
order by wm.week DESC, s.label, MD5(CONCAT(t1.name, t2.name)) ";
//limit 5";

$results = mysqli_query($conn, $sql) or die("Dead: $sql <br/>" . mysqli_error($conn));

?>

<style>
    A.Score:link {
        font-size: 10pt;
        text-decoration: none;
        color: Brown;
    }

    A.Score:visited {
        font-size: 10pt;
        text-decoration: none;
        color: Brown;
    }

    A.Score:hover {
        font-size: 10pt;
        color: Gold;
    }

    .NFLNewsDate {
        font-size: 8pt
    }
</style>

<div class="cat text-center"><?= strtoupper($weekname) ?> SCORES</div>
<div class="container">
    <?php

    while (list($weekname, $week, $team1, $leader, $leadscore, $trail, $trailscore, $label, $ot) = mysqli_fetch_row($results)) {
        ?>
        <div class="gameBox border-top border-bottom row py-1">
            <?php
            if ($label != "") {
                ?>
                <div class="boxScore text-center col-12"><?= $label ?></div>

                <?php
                //break;
            }
            $otInd = $ot ? "OT" : "";
            ?>
            <div class="boxScore col-6 px-0 text-left"><?= $leader ?><br/> <?= $trail ?></div>
            <div class="boxScore col-2 px-0 text-center"><?= $leadscore ?><br/><?= $trailscore ?>
                <span class="align-top"><?= $otInd ?></span></div>
            <div class="boxScore col-4 align-middle px-0 text-center pt-1"><a class="NFLHeadline"
                                                                              href="/activate/currentscore.php?teamid=<?= $team1 ?>&week=<?= $week ?>">Box
                    Score</a></div>

        </div>
        <?php
    }
    ?>
</div>

