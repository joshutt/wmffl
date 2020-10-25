<?
//$currentSeason = 2004;
$checkWeek = 17;
if (array_key_exists('viewseasom', $_REQUEST)) {
    $viewseason = (int) $_REQUEST['viewseasom'];
} else {
    $viewseason = $currentSeason;
    $checkWeek = $currentWeek;
}

if ($checkWeek == 0) {
    $viewseason--;
    $checkWeek = 17;
}

if (array_key_exists('vsTeam', $_REQUEST)) {
    $vsTeam = (int) $_REQUEST['vsTeam'];
} else {
    $vsTeam = 1;
}

$otherSeason = "select t.name, t.teamid
from teamnames t
join (
select t.teamid, max(t.season) as 'season'
from teamnames t
group by t.teamid) ts
ON t.teamid=ts.teamid and t.season=ts.season
ORDER BY t.name
";
$res1 = mysqli_query($conn, $otherSeason);
?>

<div align="right"><form action="teamschedule.php">
<input type="hidden" name="viewteam" value="<? print $viewteam; ?>"/>
View other teams: 
<select name="vsTeam" onChange="submit();">
<option value=""></option>
<?
while (list($newName, $newTeamid) = mysqli_fetch_array($res1)) {
    if ($newTeamid == $vsTeam) {
        $displayName = $newName;
    }
    print "<option value=\"$newTeamid\">$newName</option>";
}

?>
</select>
</form>
</div>


<h3 align="center">Vs <?= $displayName ?></h3>

<?
$SQL = <<<EOD
SELECT wm.season, if(isnull(s.label), wm.weekname, s.label) as 'weekname',
t.name, wm.week,
if(s.teama=$viewteam, s.scorea, s.scoreb) as 'score',
if(s.teamb=$viewteam, s.scorea, s.scoreb) as 'oppscore'
FROM schedule s
JOIN teamnames t ON t.teamid in (s.teama, s.teamb) and t.season=s.season
JOIN weekmap wm ON s.season=wm.season and s.week=wm.week
WHERE s.teama in ($viewteam, $vsTeam)
AND s.teamb in ($viewteam, $vsTeam)
AND t.teamid=$vsTeam
ORDER BY wm.season, wm.week
EOD;

$SQL2 = <<<EOD
SELECT sum(if(s.teama=$viewteam, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb>s.scorea, 1, 0))) as 'win',
sum(if(s.scorea=s.scoreb, 1, 0)) as 'tie',
sum(if(s.teama=$viewteam, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb<s.scorea, 1, 0))) as 'loss'
FROM schedule s
JOIN teamnames t ON t.teamid in (s.teama, s.teamb) and t.season=s.season
JOIN weekmap wm ON s.season=wm.season and s.week=wm.week
WHERE s.teama in ($viewteam, $vsTeam)
AND s.teamb in ($viewteam, $vsTeam)
AND t.teamid=$vsTeam
ORDER BY wm.season, wm.week
EOD;

$r2 = mysqli_query($conn, $SQL2) or die("Unable to complete query: ".mysqli_error($conn));
list($win, $tie, $loss) = mysqli_fetch_array($r2);
if ($win+$tie+$loss == 0) {
    $pct = 0.000;
} else {
    $pct = ($win + $tie/2) / ($win+$tie+$loss);
}

printf("<h3 align=\"center\">(%d - %d - %d - %5.3f)</h3>", $win, $loss, $tie, $pct);

$results = mysqli_query($conn, $SQL) or die("Unable to complete query: ".mysqli_error($conn));

print "<table class=\"table table-striped table-sm\">";
$wins=0; $ties=0; $loss=0;
while ($sched = mysqli_fetch_array($results)) {
    if ($sched['score'] != null && ($sched['season'] < $currentSeason || $sched['week'] < $checkWeek)) {
        print "<tr>";
        print "<td>${sched['season']}</td>";
        print "<td>${sched['weekname']}</td>";
        print "<td>${sched['name']}</td>";
        if ($sched['score'] > $sched['oppscore']) {
            print "<td>WIN</td>";
            $wins = $wins+1;
        } else if ($sched['score'] == $sched['oppscore']) {
            print "<td>TIE</td>";
            $ties = $ties+1;
        } else {
            print "<td>LOSS</td>";
            $loss = $loss+1;
        }
        print "<td>${sched['score']} - ${sched['oppscore']}</td>";
        print "</tr>";
    } else {
        print "<tr>";
        print "<td>${sched['season']}</td>";
        print "<td>${sched['weekname']}</td>";
        print "<td>${sched['name']}</td>";
        print "<td></td>";
        print "</tr>";
    }
}
print "</table>";
?>
