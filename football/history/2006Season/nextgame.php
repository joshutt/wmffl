<table>
<?
$query = <<<EOD
SELECT s.label, t1.name as 'teama', t2.name as 'teamb'
FROM schedule s, teamnames t1, teamnames t2
WHERE s.season=$thisSeason AND s.week=$thisWeek+1
AND t1.teamid=s.teama AND t2.teamid=s.teamb
AND t1.season=s.season AND t2.season=s.season
ORDER BY MD5(CONCAT(t1.name, t2.name))
EOD;
$results = mysqli_query($conn, $query) or die("Error: " . mysqli_error($conn));

$count = 0;
while ($games = mysqli_fetch_array($results)) {
    if ($count % 2 == 0) {
        $bgcolor = "dddddd";
    } else {
        $bgcolor = "ffffff";
    }
    $count++;
    
    print "<tr bgcolor=\"#$bgcolor\">";
    if ($games["label"] != "") {
        print "<th colspan=\"3\">{$games["label"]}</th>";
        print "</tr><tr>";
    }
    
    if (isset($records) && array_key_exists($games["teama"], $records)) {
        $recordA = $records[$games["teama"]];
    } else {
        $recordA = "(0-0)";
    }
    if (isset($records) && array_key_exists($games["teamb"], $records)) {
        $recordB = $records[$games["teamb"]];
    } else {
        $recordB = "(0-0)";
    }
    
    print "<td>{$games["teama"]} $recordA</td><td>vs</td>";
    print "<td>{$games["teamb"]} $recordB</td>";
    print "</tr>";
}

?>
</table>
