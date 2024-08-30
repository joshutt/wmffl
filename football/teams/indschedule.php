<?php
/**
 * @var $currentSeason int
 * @var $currentWeek int
 * @var $viewteam int
 * @var $conn mysqli
 */
//$currentSeason = 2004;
$checkWeek = 17;
if (array_key_exists('viewseason', $_REQUEST)) {
    $viewseason = (int) $_REQUEST['viewseason'];
} else {
    $viewseason = $currentSeason;
    $checkWeek = $currentWeek;
}

if ($checkWeek == 0) {
    $viewseason--;
    $checkWeek = 17;
}

$otherSeason = "select distinct season from schedule where $viewteam in (teama, teamb) order by season desc";
$res1 = mysqli_query($conn, $otherSeason);
?>

<h3 class="font-weight-bold" align="center"><?php print $viewseason; ?> Schedule</h3>

<div class="col">
    <?php
$SQL = "SELECT if(isnull(s.label), wm.weekname, s.label) as 'weekname', t.name, 
if(s.teama=$viewteam, s.scorea, s.scoreb) as 'score', 
if(s.teamb=$viewteam, s.scorea, s.scoreb) as 'oppscore',
wm.week
FROM schedule s, teamnames t, weekmap wm
WHERE s.season=$viewseason AND ((s.teama=$viewteam and s.teamb=t.teamid) OR (s.teamb=$viewteam and s.teama=t.teamid)) 
AND s.season=wm.season AND s.week=wm.week
AND t.season=s.season
ORDER BY s.season, s.week";

$results = mysqli_query($conn, $SQL) or die('Unable to complete query: ' . mysqli_error($conn));

print "<table align=\"center\" border=\"1\">";
while ($sched = mysqli_fetch_array($results)) {
    print '<tr>';
    print "<td>{$sched['weekname']}</td>";
    if ($sched['score'] != null && $sched['week'] < $checkWeek) {
        if ($sched['score'] > $sched['oppscore']) {
            print '<td>WIN</td>';
        } else if ($sched['score'] == $sched['oppscore']) {
            print '<td>TIE</td>';
        } else {
            print '<td>LOSS</td>';
        }
        print "<td>vs {$sched['name']}</td>";
        print "<td>{$sched['score']} - {$sched['oppscore']}</td>";
    } else {
        print '<td></td>';
        print "<td>vs {$sched['name']}</td>";
    }
    print '</tr>';
}
?>
    </table>
</div>

<div class="pt-4 justify-content-center col text-center">
    <form action="teamschedule.php">
        <input type="hidden" name="viewteam" value="<?php print $viewteam; ?>"/>
        View previous seasons:
        <select name="viewseason" onChange="submit();">
            <option value=""></option>
            <?php
            while (list($newSeason) = mysqli_fetch_array($res1)) {
                print "<option value=\"$newSeason\">$newSeason</option>";
            }

            ?>
        </select>
    </form>
</div>

