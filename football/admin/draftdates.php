<?php
//require_once "/base/conn.php";
//
//require_once "/utils/setup.php";
$ini = parse_ini_file('wmffl.conf');

// Database connection information
$conn = mysqli_connect('localhost', $ini['userName'], $ini['password'], $ini['dbName']);
//

$nflStartDate = '2023-09-10';
$season=2023;

$query = "SELECT t.name, d.date, min( d.attend ) as attend
FROM  `draftdate` d, user u, team t
WHERE u.userid = d.userid AND u.teamid=t.teamid AND d.date >  '$season-01-01' 
GROUP  BY u.teamid, d.date
ORDER BY d.date";

//print "*";
//print_r($conn);
//print "*";
$results = mysqli_query($conn, $query);
$date = '';
$dateList = array();

//print "a";
//print "*";
//print_r($results);
//print "*";
while ($arrayList = mysqli_fetch_array($results)) {
    //print "b";
    if ($date != $arrayList['date']) {
        $date = $arrayList['date'];
        $dateArray = array();
        $dateArray['yes'] = 0;
        $dateArray['no'] = 0;
    }

    if ($arrayList['attend'] == 'Y') {
        $dateArray['yes']++;
    } else {
        $dateArray['no']++;
    }
    $dateList[$date] = $dateArray;
}


$max = 0;
$maxArray = array();
foreach ($dateList as $date => $dateArray) {
    if ($dateArray['yes'] > $max) {
        $max = $dateArray['yes'];
        $maxArray=array($date);
    } else if ($dateArray['yes'] == $max) {
        array_push($maxArray, $date);
    }
}


$secondQuery = <<<EOD
select tn.name, max(lastUpdate)
from owners o
join draftvote dv on o.userid=dv.userid and dv.season=o.season
join teamnames tn on tn.season=o.season and tn.teamid=o.teamid
where o.season=$season
group by o.teamid
having max(dv.lastUpdate) is null
EOD;

$results = mysqli_query($conn, $secondQuery) or die('Error: ' . mysqli_error($conn));

$teamArray = array();
while ($arrayList = mysqli_fetch_array($results)) {
    array_push($teamArray, $arrayList['name']);
}

/* The Display */
print "<table border=\"1\">";
print '<tr><th>Date</th><th>Yes</th><th>No</th></tr>';
foreach($dateList as $date => $dateArray) {
    if ($dateArray['yes'] == $max) {
        print "<tr><td><b>$date</b></td><td><b>{$dateArray['yes']}</b></td><td><b>{$dateArray['no']}</b></td></tr>";
    } else {
        print "<tr><td>$date</td><td>{$dateArray['yes']}</td><td>{$dateArray['no']}</td></tr>";
    }
}
print '</table>';


print "<p>The max is $max</p>";

if (sizeof($teamArray)) {
    print '<p>Teams not voting</p>';
    foreach ($teamArray as $name) {
        print "$name<br/>";
    }

} else {
    print '<p>All teams have voted</p>';
}

?>

