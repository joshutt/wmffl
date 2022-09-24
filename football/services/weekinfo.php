<?php
require_once "utils/start.php";

$sql = "select * from weekmap where now() between startdate and enddate";
$results = mysqli_query($conn, $sql);

$outArray = array();
while($row = mysqli_fetch_assoc($results)) {
    $outArray= $row;

}

header("Content-type: text/json");
print json_encode($outArray);

