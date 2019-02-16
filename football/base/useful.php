<?
//require_once "base/conn.php";

if (isset($conn)) {
    // Determine the current season and current week
    $dateQuery = "SELECT season, week, weekname FROM weekmap ";
    $dateQuery .= "WHERE now() BETWEEN startDate and endDate ";
    $dateResult = mysql_query($dateQuery, $conn);
    list($currentSeason, $currentWeek, $weekName) = mysql_fetch_row($dateResult);
}
?>
