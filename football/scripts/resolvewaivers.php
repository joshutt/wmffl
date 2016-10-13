<?
require_once "/home/joshutt/football/base/conn.php";
require_once "/home/joshutt/football/base/useful.php";

$sql = "SELECT t.teamid, p.playerid
FROM waiverorder t, waiverpicks p, weekmap wp
WHERE t.teamid=p.teamid AND t.season=p.season AND t.week=p.week
AND t.season=wp.season AND t.week=wp.week
AND DATE_SUB(now(), INTERVAL 4 HOUR) BETWEEN wp.startdate AND wp.enddate
ORDER BY t.ordernumber, p.priority";

$waiverOrder = "SELECT wo.teamid, wo.week FROM waiverorder wo, weekmap wp 
WHERE wo.week=wp.week AND wo.season=wp.season
AND DATE_SUB(now(), INTERVAL 4 HOUR) BETWEEN wp.startdate AND wp.enddate
ORDER BY wo.ordernumber";

$results = mysql_query($sql);
$currentTeam = 0;
$teamList = array();
while (list($teamid, $playerid) = mysql_fetch_row($results)) {
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
    //print "Request Array:";
    //print_r ($reqArray);
    //print "<br/>Team Array: ";
    //print_r ($teamList);
    //print "<br/>";
}
if ($currentTeam != 0) {
    array_push($teamList, array($currentTeam, $reqArray));
}

$orderResults = mysql_query($waiverOrder) or die($waiverOrder."<br/>".mysql_error());
$orderList = array();
while (list($teamid, $week) = mysql_fetch_row($orderResults)) {
    array_push($orderList, $teamid);
    //print "$teamid<br/>";
}


//print "<pre>";
//print_r ($teamList);
//print "<br/>";
//print "</pre>";

$currentPlayers = "SELECT playerid FROM roster WHERE dateoff is null";
$results = mysql_query($currentPlayers);
$taken = array();
while (list($playerid) = mysql_fetch_row($results)) {
    array_push($taken, $playerid);
}

$waivePicks = array();
$takenPicks = array();
$count = 1;
while (count($teamList) > 0) {
    $thisTeam = array_shift($teamList);
    $reqArray = $thisTeam[1];
    while (count($reqArray) > 0) {
        $playerID = array_shift($reqArray);
        if (!in_array($playerID, $taken)) {
            array_push($taken, $playerID);
            $thisTeam[1] = $reqArray;
            //print_r ($thisTeam);
            //print "Team: ".$thisTeam[0]." gets $playerID<br/>";
            if (!array_key_exists($thisTeam[0], $waivePicks)) {
                $waivePicks[$thisTeam[0]] = array();
            }
            //$waivePicks[$thisTeam[0]] = $playerID;
            array_push($waivePicks[$thisTeam[0]], $playerID);
            $takenPicks[$playerID] = $count++;
            array_push($teamList, $thisTeam);
            $arrayPos = array_search($thisTeam[0], $orderList);
            $orderList[$arrayPos] = null;
            $orderList[$arrayPos+12] = $thisTeam[0];
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
    mysql_query($ptsQuery);
    $firstTeam = false;
}
if (!$firstTeam) {
    //print "$rostQuery<br/>$tranQuery<br/>";
    mysql_query($rostQuery);
    mysql_query($tranQuery);
    //print "$awardQuery\n";
    mysql_query($awardQuery) or die("Dead on AwardQuery: $awardQuery\n ".mysql_error());
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
mysql_query($nextWeekSQL) or die("Dead on Next Week: $nextWeekSQL\n ".mysql_error());
?>
