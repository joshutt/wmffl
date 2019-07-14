<?
include "utils/start.php";

$title = "WMFFL Teams";
include "base/menu.php";

$divisionSQL = "SELECT t.teamid, t.name as 'team', d.name as 'division'
FROM team t, division d
WHERE t.divisionid=d.divisionid and $currentSeason between d.startYear and d.endYear
ORDER BY d.name, t.name";

$results = mysqli_query($conn, $divisionSQL) or die("Error in query: " . mysqli_error($conn));
$teamList = array();
while ($teamInfo = mysqli_fetch_array($results)) {
    if (!array_key_exists($teamInfo['division'], $teamList)) {
        $teamList[$teamInfo['division']] = array();
    }
    array_push($teamList[$teamInfo['division']], $teamInfo);
}

?>

<h1 align="center">The Teams</h1>
<hr size = "1">

<table width="100%">
<tr valign="TOP">
            

<?
ksort($teamList);
foreach ($teamList as $divisionName => $division) {
    print "<td>";
    print "<table><th>$divisionName</th>";
    foreach ($division as $teamInfo) {
        print "<tr><td><a href=\"teamroster.php?viewteam=${teamInfo['teamid']}\">";
        print "${teamInfo['team']}</a></td></tr>";
    }
    print "</table></td>";
}
?>

<td><table>
<th>Defunct Teams</th>
<tr><td><a href="squirrels.php">The Fighting Squirrels</a></td></tr>
<tr><td>Kingsmen</td></tr>
</table></td>

</tr><tr>
<td>&nbsp;</td></tr>
<tr><td colspan=3><b>Other Features</b></td></tr>
<tr><td><a href="compareteams.php">Compare Rosters</a></td>
<td><a href="/transactions/displayWaiverOrder.php">Waiver Wire Order</a></td>

</tr></table>

<? include "base/footer.html"; ?>
