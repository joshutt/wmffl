<?
//require_once "/home/joshutt/football/base/conn.php";
//require_once "/home/joshutt/football/base/useful.php";
require_once "/home/joshutt/git/football/base/conn.php";
require_once "/home/joshutt/git/football/base/useful.php";

$sql = "SELECT t.teamid, p.playerid
FROM waiverorder t, waiverpicks p, weekmap wp
WHERE t.teamid=p.teamid AND t.season=p.season AND t.week=p.week
AND t.season=wp.season AND t.week=wp.week
AND DATE_SUB(now(), INTERVAL 4 HOUR) BETWEEN wp.startdate AND wp.enddate
ORDER BY t.ordernumber, p.priority";

/*
$waiverOrder = "SELECT wo.teamid, wo.week FROM waiverorder wo, weekmap wp 
WHERE wo.week=wp.week AND wo.season=wp.season
AND DATE_SUB(now(), INTERVAL 4 HOUR) BETWEEN wp.startdate AND wp.enddate
ORDER BY wo.ordernumber";
*/

$waiverOrder = "SELECT wo.teamid, wo.week, tp.TotalPts - tp.ProtectionPts - tp.TransPts as 'Remain', p.paid
FROM waiverorder wo
JOIN weekmap wp ON wo.week=wp.week AND wo.season=wp.season
JOIN transpoints tp ON wp.season=tp.season AND tp.teamid=wo.teamid
JOIN paid p ON wo.teamid=p.teamid and wp.Season=p.season
WHERE DATE_SUB(now(), INTERVAL 4 HOUR) BETWEEN wp.startdate AND wp.enddate
ORDER BY wo.ordernumber";


// Get all of the picks for each team in order
$results = mysqli_query($conn, $sql);
$currentTeam = 0;
$teamList = array();
while (list($teamid, $playerid) = mysqli_fetch_row($results)) {
    if ($teamid != $currentTeam) {
        if ($currentTeam != 0) {
            array_push($teamList, array($currentTeam, $reqArray));
        }
        $reqArray = array();
        //$newTeam = array($teamid, $reqArray);
        //array_push($teamList, $newTeam);
        $currentTeam = $teamid;
    }
    //$reqArray = $newTeam[1];
    array_push($reqArray, $playerid);
}
if ($currentTeam != 0) {
    array_push($teamList, array($currentTeam, $reqArray));
}

// Determine the exact waiver order
$orderResults = mysqli_query($conn, $waiverOrder) or die($waiverOrder . "<br/>" . mysqli_error($conn));
$orderList = array();
$allowedTrans = array();
while (list($teamid, $week, $transRemain, $paid) = mysqli_fetch_row($orderResults)) {
    array_push($orderList, $teamid);

    // If paid unlimited transactions allowed, if unpaid only free
    if ($paid) {
        $allowedTrans[$teamid] = 99;
    } else {
        $allowedTrans[$teamid] = $transRemain;
    }
}


// Get the list of players on roster
$currentPlayers = "SELECT playerid FROM roster WHERE dateoff is null";
$results = mysqli_query($conn, $currentPlayers);
$taken = array();
while (list($playerid) = mysqli_fetch_row($results)) {
    array_push($taken, $playerid);
}

// Make the picks in memory
$waivePicks = array();
$takenPicks = array();
$count = 1;
while (count($teamList) > 0) {
    $thisTeam = array_shift($teamList);
    $reqArray = $thisTeam[1];
    $thisTeamId = $thisTeam[0];
    while (count($reqArray) > 0) {
        $playerID = array_shift($reqArray);
        if (!in_array($playerID, $taken)) {
            // If not allowed to do transactions don't bother
            if ($allowedTrans[$thisTeamId] <= 0) {
                break;
            }

            array_push($taken, $playerID);
            $thisTeam[1] = $reqArray;
            if (!array_key_exists($thisTeamId, $waivePicks)) {
                $waivePicks[$thisTeamId] = array();
            }
            //$waivePicks[$thisTeam[0]] = $playerID;
            array_push($waivePicks[$thisTeamId], $playerID);
            $takenPicks[$playerID] = $count++;
            $allowedTrans[$thisTeamId]--;
            array_push($teamList, $thisTeam);
            $arrayPos = array_search($thisTeamId, $orderList);
            $orderList[$arrayPos] = null;
            $orderList[$arrayPos+12] = $thisTeamId;
            break;
        }
    }
}
//print_r($waivePicks);

//print "<pre>";
//print_r($orderList);
//print "</pre>";

$rostQuery = "INSERT INTO roster (teamid, playerid, dateon) VALUES ";
$tranQuery = "INSERT INTO transactions (teamid, playerid, date, method) VALUES ";
$awardQuery = "INSERT INTO waiveraward (season, week, pick, teamid, playerid) VALUES ";
$firstPlayer = true;
$firstTeam = true;
foreach ($waivePicks as $teamid=>$aqArray) {
    $count = 0;
    foreach ($aqArray as $playerid) {
        //print "Reconfirm $teamid got $playerid<br/>";
        if (!$firstPlayer) {
            $rostQuery .= ", ";
            $tranQuery .= ", ";
            $awardQuery .= ", ";
        } else {
            $firstPlayer = false;
        }
        $rostQuery .= "($teamid, $playerid, now())";
        $tranQuery .= "($teamid, $playerid, now(), 'Sign')";
        $count++;
        $pickNum = $takenPicks[$playerid];
        $awardQuery .= "($currentSeason, ";
        //$awardQuery .= $currentWeek-1;
        $awardQuery .= $currentWeek;
        $awardQuery .= ", $pickNum, $teamid, $playerid)";
        //$awardQuery .= "($currentSeason, $week, $pickNum, $teamid, $playerid)";
    }
    $ptsQuery = "UPDATE transpoints SET TransPts=TransPts+$count WHERE season=$currentSeason AND teamid=$teamid";
    //print "$ptsQuery<br/>";
    mysqli_query($conn, $ptsQuery);
    $firstTeam = false;
}
if (!$firstTeam) {
    //print "$rostQuery<br/>$tranQuery<br/>";
    mysqli_query($conn, $rostQuery);
    mysqli_query($conn, $tranQuery);
    //print "$awardQuery\n";
    mysqli_query($conn, $awardQuery) or die("Dead on AwardQuery: $awardQuery\n " . mysqli_error($conn));
}

$counter = 1;
$nextWeekSQL = "INSERT INTO waiverorder (season, week, ordernumber, teamid) VALUES ";
//$week++;
foreach ($orderList as $teamid) {
    if ($teamid != null) {
        if ($counter != 1) {
            $nextWeekSQL .= ", ";
        }
        //print "$counter - $teamid<br/>";
        $nextWeekSQL .= "($currentSeason, ".($currentWeek).", $counter, $teamid)";
        #$nextWeekSQL .= "($currentSeason, ".($currentWeek+1).", $counter, $teamid)";
        $counter++;
    }
}
//print $nextWeekSQL."\n";
mysqli_query($conn, $nextWeekSQL) or die("Dead on Next Week: $nextWeekSQL\n " . mysqli_error($conn));
?>
