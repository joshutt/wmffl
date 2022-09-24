<?php
define('__ROOT__', dirname(__FILE__));
$config = parse_ini_file(__ROOT__."/../conf/wmffl.conf", true);
//$ini = parse_ini_file("wmffl.conf");
$paths = $config["Paths"];
set_include_path(get_include_path().":".$paths["pearPath"].":".$paths["libPath"].":".$paths["wwwPath"].":".__ROOT__."/../conf");

//print_r($config);
//print get_include_path()."\n";

// Excluding Deprecated for now because of DB_DataObject stuff
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
error_reporting(E_ERROR);

require_once "DB/DataObject.php";
$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$options = $config['DB_DataObject'];
$debug = 5;


// DB_DataObject::debugLevel($debug);

// TODO: This is a very bad thing to do, but currently I'm dependant on it because of old php behavior
foreach ($_REQUEST as $key => $val) {
    $$key = $val;
}


// Database connection information
$ini = parse_ini_file("wmffl.conf");
$conn = mysqli_connect('localhost', $ini['userName'], $ini['password'], $ini['dbName']);

// Make sure timezone is correct
$tzQuery = "SET time_zone = 'America/New_York';";
$tzResult = mysqli_query($conn, $tzQuery);

$dateQuery = "SELECT season, week, weekname FROM weekmap ";
$dateQuery .= "WHERE now() BETWEEN startDate and endDate ";
$dateResult = mysqli_query($conn, $dateQuery);
list($currentSeason, $currentWeek, $weekName) = mysqli_fetch_row($dateResult);
