<?php

$dbname = "joshutt_oldtest";
$username = "joshutt_test";
$password = "testconn";
$dbhost = 'localhost';

$conn = mysql_connect($dbhst, $username, $password);
mysql_select_db($dbname) or die("Unable to connect to db: $dbname");