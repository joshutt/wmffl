<?php

$dbname = "joshutt_oldtest";
$username = "joshutt_test";
$password = "testconn";
$dbhost = 'localhost';

$baseRoot = "/home/joshutt/football/devel";

$conn = mysql_connect($dbhst, $username, $password);
mysql_select_db($dbname) or die("Unable to connect to db: $dbname");

set_include_path(get_include_path().":$baseRoot/lib:/home/joshutt/php");