<table cellpadding="5" cellspacing="1">
<?
include "lib/Team.php";

//$thisSeason = 2004;
//$thisWeek = 5;
$query = <<<EOD
SELECT tn.name as 'team', d.name as 'division',
       sum(if(t.teamid=s.teama, s.scorea, s.scoreb)) as 'ptsfor',
       sum(if(t.teamid=s.teama, s.scoreb, s.scorea)) as 'ptsagt',
       sum(IF(t.teamid=s.teama AND s.scorea>s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb>s.scorea, 1, 0))) as 'win',
       sum(IF(t.teamid=s.teama AND s.scorea<s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb<s.scorea, 1, 0))) as 'lose',
       sum(IF(s.scorea=s.scoreb, 1, 0)) as 'tie',

       sum(if(t.divisionid=t2.divisionid, if(t.teamid=s.teama, s.scorea, s.scoreb), 0)) as 'divpf',
       sum(if(t.divisionid=t2.divisionid, if(t.teamid=s.teama, s.scoreb, s.scorea), 0)) as 'divpa',
       sum(if(t.divisionid=t2.divisionid, IF(t.teamid=s.teama AND s.scorea>s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb>s.scorea, 1, 0)),0)) as 'divwin',
       sum(if(t.divisionid=t2.divisionid, IF(t.teamid=s.teama AND s.scorea<s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb<s.scorea, 1, 0)),0)) as 'divlose',
       sum(if(t.divisionid=t2.divisionid, IF(s.scorea=s.scoreb, 1, 0),0)) as 'divtie'


FROM schedule s
 JOIN team t on t.TeamID in (s.TeamA, s.TeamB)
  JOIN teamnames tn on t.TeamID=tn.teamid AND tn.season=s.season
  JOIN division d ON d.divisionid=tn.divisionid and d.startYear <= s.Season and (d.endYear >= s.Season or d.endYear is null)
  JOIN team t2 ON t2.teamid in (s.teama, s.teamb) AND t2.teamid <> t.teamid
WHERE s.season =$thisSeason
  AND s.week<=$thisWeek
  AND s.week<=14
GROUP BY d.name, tn.name
EOD;

$results = mysqli_query($conn, $query) or die("Error: " . mysqli_error($conn));
$count =0;
$teamArray = array();
while ($row = mysqli_fetch_array($results)) {
#    print_r($row);
   // $divRar = 

    $t = new Team($row["team"], $row["division"], $count);
    $rec = array($row["win"], $row["lose"], $row["tie"]);
    $div = array($row["divwin"], $row["divlose"], $row["divtie"]);
    $t->record = $rec;
    $t->divRecord = $div;
    $t->ptsFor = $row["ptsfor"];
    $t->ptsAgt = $row["ptsagt"];
    $t->divPtsFor = $row["divpf"];
    $t->divPtsAgt = $row["divpa"];
    array_push($teamArray, $t);
}

//print_r($teamArray);
usort($teamArray, "orderteam");
//print_r($teamArray);
$records = array();
$division = null;
foreach($teamArray as $t) {
    $thisDiv = $t->division;
    if ($division != $thisDiv) {
        print <<< EOD
<tr><th colspan="12"><font size="+1">$thisDiv</font></th></tr>
<tr><th></th><th colspan="4">Overall</th><th></th>
<th colspan="3">In Division</th></tr>
<tr><th>Team</th><th>W</th><th>L</th><th>T</th>
<th>PCT</th>
<th width="10"></th>
<th>W</th><th>L</th><th>T</th>
<th width="10"></th>
<th>PF</th><th>PA</th>

EOD;
        
        $division = $thisDiv;
        $count = 0;
    }

    if ($count % 2 == 0) {
        $bgcolor = "dddddd";
    } else {
        $bgcolor = "ffffff";
    }
    $count++;

    if ($t->record[2] > 0) {
        $records[$t->name] = sprintf("(%d-%d-%d)", $t->record[0], $t->record[1], $t->record[2]);
    } else {
        $records[$t->name] = sprintf("(%d-%d)", $t->record[0], $t->record[1]);
    }

    print <<< EOD
<tr bgcolor="$bgcolor"><td>{$t->name}</td>
<td align="center">{$t->record[0]}</td>
<td align="center">{$t->record[1]}</td>
<td align="center">{$t->record[2]}</td>
EOD;
    printf ("<td>%5.3f</td>",($t->getWinPCT()));
    print <<< EOD

<td>&nbsp;</td>
<td align="center">{$t->divRecord[0]}</td>
<td align="center">{$t->divRecord[1]}</td>
<td align="center">{$t->divRecord[2]}</td>
<td>&nbsp;</td>
<td align="center">{$t->ptsFor}</td>
<td align="center">{$t->ptsAgt}</td>
EOD;
    /*
    printf ("<td>%5.3f</td>",($t->getDivWinPCT()));
    print <<< EOD
<td>{$t->divPtsFor}</td><td>{$t->divPtsAgt}</td>
</tr>
EOD;
    */
}

?>
</table>
