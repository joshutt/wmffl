<?
//require_once "base/conn.php";

if (isset($conn)) {
    // Determine the current season and current week
    $dateQuery = "SELECT season, week, weekname FROM weekmap ";
    $dateQuery .= "WHERE now() BETWEEN startDate and endDate ";
    $dateResult = mysqli_query($conn, $dateQuery);
    list($currentSeason, $currentWeek, $weekName) = mysqli_fetch_row($dateResult);
}
?>
