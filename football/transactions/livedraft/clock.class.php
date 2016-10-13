<?
require_once "utils/start.php";

class Clock {


}

function getPreviousPickTime() {
    // Get the previous pick time
    $sql = "SELECT max(pickTime) as 'pickTime', max(c.value) as 'startTime'  FROM draftpicks p JOIN config c ON c.key='draft.full.start'";
    $results = mysql_query($sql) or die ("Unable to get max time: ".mysql_error()); 
    $rows = mysql_fetch_assoc($results);
    $pickTime = strtotime($rows["pickTime"]);
    $startTime = $rows["startTime"];
    return max($pickTime, $startTime);
    //return $pickTime>$startTime ? $pickTime : $startTime;
}


function getExtraTime($season, $round, $pick) {
    // Get any clock stop time
    $sql = "SELECT * FROM draftclockstop WHERE season=$season AND round=$round and pick=$pick";
    $results = mysql_query($sql) or die ("Unable to get clock stop: ".mysql_error()); 
    $totalExtra = 0;
    while ($rows = mysql_fetch_assoc($results)) {
        $timeStopped = strtotime($rows["timeStopped"]);
        $timeStarted = strtotime($rows["timeStarted"]);
//	print $timeStarted;
//	print "***";
        if ($timeStarted == null) {
            $timeStarted = time();
        }
//	print $timeStarted;
//	print "***";
//	print $timeStopped;
        $totalExtra += $timeStarted - $timeStopped;
    }
    return $totalExtra;
}

function getCurrentPick($season) {
    $sql = "SELECT round, pick FROM draftpicks WHERE season=$season and playerid is null ORDER BY round, pick";
    $results = mysql_query($sql) or die ("Unable to get current pick: ".mysql_error()); 
    $rows = mysql_fetch_assoc($results);
    $returnArray = array($rows["round"], $rows["pick"]);
    return $returnArray;
}

function getTimeAvail($team) {
    $sql = "SELECT value FROM config WHERE `key`='draft.team.$team' ";
    $results = mysql_query($sql) or die ("Unable to get time available: ".mysql_error()); 
    $rows = mysql_fetch_assoc($results);
    $remainTime = $rows["value"];
    return $remainTime;
}

function clockRunning() {
    $sql = "SELECT value from config WHERE `key`='draft.clock.run' ";
    $results = mysql_query($sql) or die ("Unable to get time available: ".mysql_error()); 
    $rows = mysql_fetch_assoc($results);
    $clockRun = $rows["value"];
    return $clockRun;
}


function getTotalTimeUsed($season, $round=0, $pick=0) {

    if ($round == 0) {
        list($round, $pick) = getCurrentPick($season);
    }

    // Calculate total time used
    if ($round == 1 && $pick == 1) {
        $extra = 0;
    } else {
        $extra = getExtraTime($season, $round, $pick);
    }
    
    //print "Time: ".time();
    //print "Extra: $extra";
//    error_log("Time: ".time()."\n", 3, "check.log");
//    error_log("Extra: ".$extra."\n", 3, "check.log");
    $prev = getPreviousPickTime();
//    error_log("Previous: ".$prev."\n", 3, "check.log");
    $totalTime = time() - $prev - $extra;
//    error_log("Total Time: ".$totalTime."\n", 3, "check.log");

    return $totalTime;
}
