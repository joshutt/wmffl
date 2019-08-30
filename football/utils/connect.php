<?php
/**
 * This will open the session and a database connection.  Using the database modules that work in php7
 *
 * The following session variables are defined in here:
 *   $currentWeek - The current season week
 *   $currentSeason - The current season year
 *   $weekName - The name of the current week
 *   $lastFetch - The time stamp of the last time week info was retrieved
 *
 * Created by IntelliJ IDEA.
 * User: Josh
 * Date: 9/30/2018
 * Time: 6:22 PM
 */

require_once "setup.php";

// Start the sessions, ensure every page is in session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Establish database connections
$conn = mysqli_connect('localhost', $ini['userName'], $ini['password'], $ini['dbName']);

// Determine the current season and current week, but not every time, use cachin
//if (!isset($_SESSION["lastFetch"]) || time() > $lastFetch + 60 * 60) {
$dateQuery = <<<EOD
SELECT w1.season, w1.week, w1.weekname, w2.weekname as 'previous' FROM weekmap w1, weekmap w2 
WHERE now() BETWEEN w1.startDate and w1.endDate 
and IF(w1.week=0, w2.season=w1.season-1 and w2.week=16, w2.week=w1.week-1 and w2.season=w1.season) 
EOD;

$dateResult = mysqli_query($conn, $dateQuery);
list($_SESSION["currentSeason"], $_SESSION["currentWeek"], $_SESSION["weekName"], $_SESSION["previousWeekName"]) = mysqli_fetch_row($dateResult);
if ($_SESSION["currentWeek"] == 0) {
    $_SESSION["previousWeekSeason"] = $_SESSION["currentSeason"] - 1;
    $_SESSION["previousWeek"] = 16;
} else {
    $_SESSION["previousWeekSeason"] = $_SESSION["currentSeason"];
    $_SESSION["previousWeek"] = $_SESSION["currentWeek"] - 1;
}
$_SESSION["lastFetch"] = time();

//}

foreach ($_SESSION as $key => $value) {
    ${$key} = $value;
    $GLOBALS[$key] = $value;
}

if (!isset($isin) || empty($isin)) {
    $isin = false;
}
