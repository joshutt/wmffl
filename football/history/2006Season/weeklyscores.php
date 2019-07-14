<table cellpadding="2">
<?
$weekQuery = <<<EOD
SELECT t1.name as 'namea', s.scorea, t2.name as 'nameb', s.scoreb, s.label
FROM schedule s, teamnames t1, teamnames t2
WHERE s.teama = t1.teamid
AND s.teamb = t2.teamid
AND s.season=$thisSeason AND t1.season=$thisSeason AND t2.season=$thisSeason
AND s.week=$thisWeek
ORDER BY s.label, MD5(CONCAT(t1.name, t2.name))
EOD;
$results = mysqli_query($conn, $weekQuery) or die("SQL Error: " . mysqli_error($conn));

$count = 0;
$gameArray = array();
while ($row = mysqli_fetch_array($results)) {
    if ($count % 2 == 0) {
        $bgcolor="dddddd";
    } else {
        $bgcolor="ffffff";
    }
    $count++;

    if ($row["label"] != null && $row["label"] != '') {
        print "<tr><th colspan=\"5\" align=\"center\" bgcolor=\"#$bgcolor\">{$row["label"]}</th></tr>";
        if ($bgcolor == "dddddd") {
            $bgcolor = "ffffff";
        } else {
            $bgcolor = "dddddd";
        }
    }

    if ($row["scorea"] > $row["scoreb"]) {
        $winTeam = $row["namea"];
        $winScore = $row["scorea"];
        $loseTeam = $row["nameb"];
        $loseScore = $row["scoreb"];
    } else {
        $winTeam = $row["nameb"];
        $winScore = $row["scoreb"];
        $loseTeam = $row["namea"];
        $loseScore = $row["scorea"];
    }
    
    print <<<EOD
<tr bgcolor="#$bgcolor"><td>$winTeam</td>
<td width="40" align="center">$winScore</td>
<td width="10"></td>
<td>$loseTeam</td>
<td width="40" align="center">$loseScore</td></tr>
EOD;

    $sinGam = array($winTeam, $winScore, $loseTeam, $loseScore);
    array_push($gameArray, $sinGam);
}
?>
</table>
