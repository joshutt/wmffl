<?php
require_once "utils/start.php";

if (!$isin) {
    exit();
}

$teamId = $_SESSION["teamnum"];

// Remove this team's preselections
$sql = "DELETE FROM draftPickHold WHERE teamid=$teamId";
mysql_query($sql) or die ("Unable to remove hold: ".mysql_error());
