<?php
require_once "utils/setup.php";

// Database connection information
$conn = mysql_connect('localhost',$ini["userName"],$ini["password"]);
mysql_select_db($ini["dbName"]);
