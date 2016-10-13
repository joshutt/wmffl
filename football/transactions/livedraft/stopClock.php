<?
require_once "utils/start.php";
require "clock.class.php";

if ($isin && $usernum==2) {
    list($round, $pick) = getCurrentPick($currentSeason);

    if (isset($_REQUEST["start"])) {
        $sql = "UPDATE config SET value='true' WHERE `key`='draft.clock.run'";
        print "Start";
        $sql2 = "UPDATE draftclockstop SET timeStarted=now() where season=$currentSeason and round=$round and pick=$pick and timeStarted is null";
    } else if (isset($_REQUEST["stop"])) {
        $sql = "UPDATE config SET value='false' WHERE `key`='draft.clock.run'";
        print "Stop";
        $sql2 = "INSERT INTO draftclockstop (season, round, pick, timeStopped) VALUES ($currentSeason, $round, $pick, now())";
    }

    mysql_query($sql) or die("Dead: ".mysql_error());
    mysql_query($sql2) or die("Unable to log stopped clock: ".mysql_error());

    $sql = "UPDATE config SET value='".time()."' WHERE `key`='draft.clock.start'";
    mysql_query($sql) or die("Dead: ".mysql_error());

    if ($round==1 && $pick==1) {
        $sql = "UPDATE config SET value='".time()."' WHERE `key`='draft.full.start'";
        mysql_query($sql) or die("Dead: ".mysql_error());
    }
}

?>
