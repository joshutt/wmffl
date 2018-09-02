<?
include "lib/Team.php";

//$thisSeason = 2004;
//$thisWeek = 5;
$query = <<<EOD
SELECT tn.name as 'team', d.name as 'division', t.teamid as 'teamid',
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
JOIN team t on t.teamid in (s.teama, s.teamb)
JOIN teamnames tn ON t.teamid=tn.teamid AND tn.season=s.season
JOIN division d ON d.divisionid=t.divisionid AND d.startYear <= s.season and (d.endYear >= s.season or d.endYear is null)
JOIN team t2 ON t2.teamid in (s.teama, s.teamb) and t2.teamid<>t.teamid
JOIN weekmap wm ON wm.season=s.season and wm.week=s.week and (DATE(wm.endDate) <= DATE(DATE_ADD(now(), INTERVAL -5 HOUR)) or wm.week=1)
WHERE s.season =$thisSeason
AND s.week<=$thisWeek
AND s.week<=14
GROUP BY d.name, tn.name

EOD;
#print "<pre>$query</pre>";

$secondQuery = <<<EOD
SELECT t.teamid as 'teamid', t2.teamid as 'oppid', if(t.teamid=s.teama, s.scorea, s.scoreb) as 'ptsfor', if(t.teamid=s.teama, s.scoreb, s.scorea) as 'ptsagt', wm.week, tn.divisionid, tn2.divisionid
FROM schedule s
JOIN team t ON t.teamid in (s.teama, s.teamb)
JOIN teamnames tn ON t.teamid=tn.teamid AND tn.season=s.season
JOIN division d ON d.divisionid=t.divisionid AND d.startYear <= s.season and (d.endYear >= s.season or d.endYear is null)
JOIN team t2 ON t2.teamid in (s.teama, s.teamb) AND t2.teamid <> t.teamid
JOIN teamnames tn2 ON t2.teamid=tn2.teamid and tn2.season=s.season
JOIN weekmap wm ON wm.season=s.season and wm.week=s.week and DATE(wm.enddate)<=DATE(DATE_ADD(now(), INTERVAL -5 HOUR))
WHERE s.season =$thisSeason
AND s.week<=$thisWeek
AND s.week<=14
EOD;


$results = mysql_query($query) or die("Error: ".mysql_error());
$count =0;
$teamArray = array();
while ($row = mysql_fetch_array($results)) {
    $t = new Team($row["team"], $row["division"], $row["teamid"]);
    $rec = array($row["win"], $row["lose"], $row["tie"]);
    $div = array($row["divwin"], $row["divlose"], $row["divtie"]);
//    $t->record = $rec;
    $t->divRecord = $div;
//    $t->ptsFor = $row["ptsfor"];
//    $t->ptsAgt = $row["ptsagt"];
    $t->divPtsFor = $row["divpf"];
    $t->divPtsAgt = $row["divpa"];
    $t->allRef = &$teamArray;
    $teamArray[$row["teamid"]] = $t;
    //array_push($teamArray, $t);
}


$results = mysql_query($secondQuery) or die("Second Error: ".mysql_error());
while ($row = mysql_fetch_array($results)) {
    //print_r($row);
    $teamid = $row["teamid"];
    $opp = $row["oppid"];
    $pts = $row["ptsfor"];
    $agst = $row["ptsagt"];
 //   print "$teamid - ";
    $teamArray[$teamid]->addGame($opp, $pts, $agst, 99);
}

/*
print "<pre>";
print_r($teamArray);
print "</pre>";
*/
usort($teamArray, "orderteam");


if (!isset($display) or $display == 1) {
    $records = array();
    print "<table cellpadding=\"5\" cellspacing=\"1\">";
    foreach ($teamArray as $t) {
        $thisDiv = $t->division;
        if ($division != $thisDiv) {
            print <<< EOD
<tr height="20"><th>&nbsp;</th></tr>
<tr><th colspan="12"><font size="+1">$thisDiv</font></th></tr>
<tr><th></th><th colspan="4">Overall</th><th></th>
<th colspan="3">In Division</th></tr>
<tr><th>Team</th><th>W</th><th>L</th><th>T</th>
<th>PCT</th>
<th width="10"></th>
<th>W</th><th>L</th><th>T</th>
<th width="10"></th>
<th>SOV</th>
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
<tr bgcolor="$bgcolor"><td>{$clinchedList[$t->name]}<a href="/teams/teamschedule.php?viewteam={$t->teamid}">{$t->name}</a></td>
<td align="center">{$t->record[0]}</td>
<td align="center">{$t->record[1]}</td>
<td align="center">{$t->record[2]}</td>
EOD;
        printf("<td>%5.3f</td>", ($t->getWinPCT()));
        print <<< EOD

<td>&nbsp;</td>
<td align="center">{$t->divRecord[0]}</td>
<td align="center">{$t->divRecord[1]}</td>
<td align="center">{$t->divRecord[2]}</td>
<td>&nbsp;</td>
<td align="center">{$t->getPrintSOV($teamArray)}</td>
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
    print "</table>";
}
