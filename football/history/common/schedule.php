<?php
if ($thisSeason < $currentSeason) {
    $thisWeek = 17;
} else {
    $thisWeek = $currentWeek;
}

$sql = <<<EOD
SELECT s.week, t1.name, s.scorea, t2.name, s.scoreb, w.weekname,
MONTHNAME(w.displayDate), DAYOFMONTH(w.displayDate),
DAYNAME(w.displayDate), MONTHNAME(DATE_SUB(w.enddate, INTERVAL 1 DAY)), DAYOFMONTH(DATE_SUB(w.enddate, INTERVAL 1 DAY)),
s.label, s.postseason
FROM schedule s JOIN weekmap w ON s.season=w.season and s.week=w.week
LEFT JOIN teamnames t1 ON s.teama=t1.teamid and s.season=t1.season
LEFT JOIN teamnames t2 ON s.teamb=t2.teamid and s.season=t2.season
WHERE s.season=$thisSeason
ORDER BY s.week, s.label, MD5(CONCAT(t1.name, t2.name))
EOD;

$byeSQL = <<<EOD
SELECT t.nflteam, t.name, t.nickname, wm.season, wm.week
FROM nflteams t
JOIN weekmap wm 
LEFT JOIN nflgames g ON t.nflteam in (g.homeTeam, g.roadTeam) and g.season=wm.season and g.week=wm.week
where wm.season=$thisSeason and g.week is null and wm.week>0
group by wm.week, t.nflteam
order by wm.week, t.name
EOD;

$byeResults = mysqli_query($conn, $byeSQL);
$lastName = "";
$lastWeek = 0;
$byeList = array();
$string = "";
while ($rows = mysqli_fetch_assoc($byeResults)) {
    $week = $rows['week'];
    $teamName = $rows['name'];
    if ($teamName == 'New York' or $teamName == 'Los Angeles') {
        $teamName .= " ".$rows['nickname'];
    }
    
    if ($lastWeek == $week) {
        $string .= ", $teamName";
    } else {
        $byeList[$lastWeek] = $string;
        $string = $teamName;
        $lastName = $teamName;
        $lastWeek = $week;
    }
}
$byeList[$lastWeek] = $string;

$title = "WMFFL Schedule";
$cssList = array("/base/css/schedule.css");
include "base/menu.php";
?>

<H1 Align=Center><? print $thisSeason;?> Schedule</H1>
<HR size = "1"><CENTER>

<?
$results = mysqli_query($conn, $sql);

$listWeek = 0;
$lastLabel = "";
while ($row = mysqli_fetch_array($results)) {
    if ($row[0] != $listWeek || $row[11] != $lastLabel) {
        if ($listWeek != 0) {
            ?>
</tbody>
</table>
</div>
<br/>
<?php
        }

        
        $anchorName = str_replace(" ","",$row[5]);
        $displayWeek = $row[5];
        if ($row[11] != "") {
            $displayWeek = $row[11];
        }

?>
        <a name="$anchorName"/>
        <div class="SLTables1">
<table width="550" cellspacing="1" cellpadding="2" border="0" class="SLTables1">
    <tbody>
    <tr class="bg0" align="center">
        <td class="bg0" colspan="6">
            <font class="bg0font"><?= $displayWeek ?></font>
        </td>
    </tr>
    <tr class="bg1" align="center">
        <td class="bg1" colspan="6">
            <font class="bg1font"><b><?= $row[8] ?>, <?= $row[6]?> <?=$row[7]?></b></font>
        </td>
    </tr>
<?php
        if (array_key_exists($row[0], $byeList)) {
            $byeString = $byeList[$row[0]];
?>
    <tr id="main" class="bg4" align="left">
        <td class="bg4" colspan="6">
            <font class="bg4font">NFL Byes: <?=$byeString?></font>
        </td>
    </tr>
<?php
        }
        $listWeek = $row[0];
        $lastLabel = $row[11];
    }
    if ($row[4] > $row[2]) {
        $winName = $row[3];
        $winScore = $row[4];
        $loseName = $row[1];
        $loseScore = $row[2];
    } else {
        $winName = $row[1];
        $winScore = $row[2];
        $loseName = $row[3];
        $loseScore = $row[4];
    }
?>

<tr class="bg2" valign="middle" height="17" align="right">

<?php
    if ($row[0] < $thisWeek) {
?>
    <td align="left" width="235"><?=$winName?></td>
    <td align="center" width="40"><?=$winScore?></td>
    <td align="left" width="20">&nbsp;</td>
    <td align="left" width="235"><?=$loseName?></td>
    <td align="center" width="40"><?=$loseScore?></td>
<?php
    } else {
?>
        <td align="left" width="235"><?=$winName?></td><td width="40">&nbsp;</td>
        <td align="left" width="20">vs</td><td width="40">&nbsp;</td>
        <td align="left" width="235"><?=$loseName?></td>
<?php } ?>
    </tr>
<?php
}
?>

</tbody></table>
</div><br/>

<? include "base/footer.html"; ?>
