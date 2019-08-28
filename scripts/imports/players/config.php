<?php

/*
$dbname = "joshutt_oldtest";
$username = "joshutt_test";
$password = "testconn";
 */
$dbname = "joshutt_oldwmffl";
$username = "joshutt_footbal";
$password = "wmaccess";
$dbhost = 'localhost';

$conn = mysqli_connect($dbhost, $username, $password, $dbname) or die("Unable to connect to db: $dbname");

