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

$conn = mysql_connect($dbhst, $username, $password);
mysql_select_db($dbname) or die("Unable to connect to db: $dbname");
