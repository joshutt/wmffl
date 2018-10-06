<?
require_once "utils/start.php";

$seasonArray = array();

/**************************************************************
 * Get the Playoff record
 **************************************************************/
$recordQuery = <<<EOD
SELECT if (playoffs=0, 'Toilet Bowl', 'Playoffs') as `event`,
sum(if(s.teama=$viewteam, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb > s.scorea, 1, 0))) as `win`,
sum(if(s.teama=$viewteam, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb < s.scorea, 1, 0))) as `lose`,
sum(if(s.scorea=s.scoreb, 1, 0)) as `tie`
FROM schedule s
WHERE $viewteam in (s.teama, s.teamb) and postseason=1
group by playoffs desc
EOD;

$result = mysqli_query($conn, $recordQuery);

while ($recordList = mysqli_fetch_array($result)) {
    $newArray = array($recordList["event"], $recordList["win"], $recordList["lose"], $recordList["tie"]);

    if ($recordList["win"] + $recordList["tie"] + $recordList["lose"] == 0) {
        array_push($newArray, "0.000");
    } else {
        $pct = ($recordList["win"] * 1.0 + $recordList["tie"] * 0.5) / ($recordList["win"] + $recordList["lose"] + $recordList["tie"]);
        array_push($newArray, sprintf("%5.3f", $pct));
    }

    array_push($seasonArray, $newArray);
}


/**************************************************************
 * Get the Regular Season records, by season
 **************************************************************/
$recordQuery = <<<EOD
SELECT s.season,
sum(if(s.teama=$viewteam, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb > s.scorea, 1, 0))) as `win`,
sum(if(s.teama=$viewteam, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb < s.scorea, 1, 0))) as `lose`,
sum(if(s.scorea=s.scoreb, 1, 0)) as `tie`
FROM schedule s
WHERE $viewteam in (s.teama, s.teamb) and postseason=0
and if($currentWeek = 0, s.season<>$currentSeason, true)
GROUP BY s.season
order by s.season desc
EOD;

$result = mysqli_query($conn, $recordQuery);

while ($recordList = mysqli_fetch_array($result)) {
    $newArray = array($recordList["season"], $recordList["win"], $recordList["lose"], $recordList["tie"]);

    if ($newArray[1]+$newArray[2]+$newArray[3] != 0) {
        $pct = ($recordList["win"] * 1.0 + $recordList["tie"] * 0.5) / ($recordList["win"] + $recordList["lose"] + $recordList["tie"]);
        array_push($newArray, sprintf("%5.3f", $pct));

        array_push($seasonArray, $newArray);
    }
}


/*********************************************************
 * Calculate all time record
 *********************************************************/
$wins = 0; $lose=0; $tie=0;
foreach ($seasonArray as $innerArray) {
    $wins += $innerArray[1];
    $lose += $innerArray[2];
    $tie += $innerArray[3];
}

$newArray = array("All-Time", $wins, $lose, $tie);
$pct = ($wins*1.0 + $tie*0.5) / ($wins+$lose+$tie);
array_push($newArray, sprintf("%5.3f", $pct));
$seasonArray = array_reverse($seasonArray);
array_push($seasonArray, $newArray);
$seasonArray = array_reverse($seasonArray);


/*********************************************************
 * Get playoff results
 *********************************************************/
$playoffQuery =<<<EOD
    SELECT if(s.playoffs=0, 'Toilet Bowl', if(s.championship=0, 'Playoffs', 'Championship')) as `event`,
    s.season, n.name as 'otherTeam',
    if (s.TeamA=$viewteam, s.scorea, s.scoreb) as 'myscore',
    if (s.TeamA=$viewteam, s.scoreb, s.scorea) as 'otherscore'
    FROM schedule s, teamnames n
    WHERE $viewteam in (s.TeamA, s.TeamB) and s.postseason=1
    and n.season=s.season and n.teamid<>$viewteam and n.teamid in (s.TeamA, s.TeamB)
    order by s.season asc, s.week asc
EOD;

$result = mysqli_query($conn, $playoffQuery) or die("Mysql error: " . mysqli_error());
$playoffResults = array();

while ($recordList = mysqli_fetch_array($result)) {
    $singleGame = array();
    
    if ($recordList["myscore"] > $recordList["otherscore"]) {
        $label = "Beat";
    } else {
        $label = "Lost to";
    }
    $singleGame[0] = $recordList["event"]." ".$recordList["season"];
    $singleGame[1] = $label." ".$recordList["otherTeam"];
    $singleGame[2] = $recordList["myscore"]."-".$recordList["otherscore"];
    array_push($playoffResults, $singleGame);
}



/*********************************************************
 * Get titles
 *********************************************************/
$titleQuery =<<<EOD
    select t.season, t.type, d.name as 'divName'
    from titles t, teamnames n, division d
    where t.teamid=$viewteam and t.teamid=n.teamid and t.season=n.season
    and n.divisionid=d.divisionid and t.season between d.startYear and d.endYear
    order by t.season asc
EOD;

$result = mysqli_query($conn, $titleQuery) or die("Mysql error: " . mysqli_error());
$leagueTitles = array();
$divisionTitles = array();

while ($titles = mysqli_fetch_array($result)) {
    if ($titles["type"] == "League") {
        array_push($leagueTitles, $titles["season"]);
    } else if ($titles["type"] == "Division") {
        $pair = array($titles["season"], $titles["divName"]);
        array_push($divisionTitles, $pair);
    }
}


/*********************************************************
 * Get Past Names
 *********************************************************/
$namedArray = array();
$nameQuery = "select season, name from teamnames where teamid=$viewteam order by season asc";
$result = mysqli_query($conn, $nameQuery);
$prevName = "";
$startSeason = 0;
while ($nameSet = mysqli_fetch_array($result)) {
    if ($nameSet["name"] != $prevName) {
        if ($startSeason != 0) {
            $oneName = array("start" => $startSeason, "end" => $nameSet["season"]-1, "name" => $prevName);
            array_push($namedArray, $oneName);
        }
        $startSeason = $nameSet["season"];
        $prevName = $nameSet["name"];
    }
}
$oneName = array("start" => $startSeason, "end" => 0, "name" => $prevName);
array_push($namedArray, $oneName);

/*********************************************************
 * Get Owners
 *********************************************************/
$ownerArray = array();
$ownerQuery = "SELECT u.name, o.season, o.primary from owners o, user u where o.userid=u.userid and o.teamid=$viewteam order by o.season asc, o.primary asc";
$result = mysqli_query($conn, $ownerQuery) or die("Die: " . mysqli_error());
$prevName = "";
$finalName = "";
$startSeason = 0;
while ($ownerSet = mysqli_fetch_array($result)) {
    if ($ownerSet["primary"] == 0) {
        $finalName .= " and ".$ownerSet["name"];
        continue;
    }
    $newFinalName = $ownerSet["name"] . $finalName;
    $finalName = $newFinalName;
    
    if ($finalName != $prevName) {
        if ($startSeason != 0) {
            $oneName = array("start" => $startSeason, "end" => $ownerSet["season"]-1, "name" => $prevName);
            array_push($ownerArray, $oneName);
        }
        $startSeason = $ownerSet["season"];
        $prevName = $finalName;
    }
    $finalName = "";
}
$oneName = array("start" => $startSeason, "end" => 0, "name" => $prevName);
array_push($ownerArray, $oneName);


/*********************************************************
 * Print the display
 *********************************************************/
print <<<EOD
<table width="100%">
<TR><TD ALIGN=Center COLSPAN=2>
	<A NAME="History"><H3>History</H3></A>
	<TABLE WIDTH=75%>
	<TH>Record
	<TR><TD WIDTH=20%><B>YEAR</B></TD><TD WIDTH=8%><B>W</B></TD><TD WIDTH=8%><B>L</B></TD>
	<TD WIDTH=4%><B>T</B></TD><TD WIDTH=10%><B>PCT</B></TD><TD WIDTH=50%><B>FINISH</B></TD></TR>
EOD;
$count = 0;
foreach ($seasonArray as $innerArray) {
    print "<tr>";
    if ($count == 0) {
        $startLabel = "<b>";
        $endLabel = "</b>";
        $count++;
    } else {
        $startLabel = "";
        $endLabel="";
    }
    foreach ($innerArray as $item) {
        print "<td>$startLabel$item$endLabel</td>";
    }
    print "</tr>";
}
print "</table>";
print "</td></tr>";


// Print playoff results
print "<TR><TD ALIGN=Center COLSPAN=2><TABLE width=75%><TH>PostSeason Results</th>";
foreach ($playoffResults as $singleGame) {
    print "<tr><td>{$singleGame[0]}</td>";
    print "<td>{$singleGame[1]}</td>";
    print "<td>{$singleGame[2]}</td></tr>";
}
print "</TABLE></TD></TR>";


// Print titles
print <<<EOD
<TR><TD WIDTH="50%" VALIGN="Top">
	<TABLE ALIGN=Left>
	<TH>Division Titles</th>
EOD;
foreach ($divisionTitles as $divTitle) {
    $season = $divTitle[0];
    $divName = $divTitle[1];
	print "<TR><TD>$season</TD><TD>$divName Title</TD></TR>";
}
print "</TABLE></TD>";

print <<<EOD
<TD WIDTH=50% VALIGN=Top>
	<TABLE>
	<TH>League Titles</th>
EOD;
foreach ($leagueTitles as $legTitle) {
	print "<TR><TD>$legTitle</TD><TD>WMFFL Champions</TD></TR>";
}
print "</TABLE></TD></TR>";


// Print owners
print <<<EOD
<TR><TD WIDTH=50% VALIGN=Top>
	<TABLE>
	<TH>Past Owners</th>
EOD;
foreach ($ownerArray as $owners) {
    $startDate = $owners["start"];
    $endDate = $owners["end"];
    if ($endDate == 0) {
        $endDate = "";
    }
    $ownerName = $owners["name"];
    if ($startDate==$endDate) {
        print "<tr><td>$startDate</td><td>$ownerName</td></tr>";
    } else {
        print "<tr><td>$startDate-$endDate</td><td>$ownerName</td></tr>";
    }
}
print "</TABLE></TD>";

print <<<EOD
<TD WIDTH=50% VALIGN=Top>
	<TABLE>
	<TH>Past Names</th>
EOD;
foreach ($namedArray as $names) {
    $startDate = $names["start"];
    $endDate = $names["end"];
    if ($endDate == 0) {
        $endDate = "";
    }
    $teamName = $names["name"];
    if ($startDate == $endDate) {
        print "<tr><td>$startDate</td><td>$teamName</td></tr>";
    } else {
        print "<tr><td>$startDate-$endDate</td><td>$teamName</td></tr>";
    }
}
print "</TABLE></TD></TR>";
