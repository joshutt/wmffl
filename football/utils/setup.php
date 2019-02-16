<?php
// Paths to config files should be set in .user.ini file
$ini = parse_ini_file("wmffl.conf");

require_once "DB/DataObject.php";

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
#$config = parse_ini_file("$SCRIPT_PATH/dataobjects.ini", TRUE);
#$config = parse_ini_file("$CONF_PATH/wmffl.conf", TRUE);
$config = parse_ini_file("wmffl.conf", TRUE);
$options = $config['DB_DataObject'];
$debug = 5;

error_reporting(E_ALL & ~E_DEPRECATED);

#DB_DataObject::debugLevel($debug);

