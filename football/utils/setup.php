<?php
date_default_timezone_set('America/New_York');

#$SCRIPT_PATH = "/home/joshutt/git/lib";
$DIR_ROOT = "C:/Users/Josh/IdeaProjects/wmffl";
$SCRIPT_PATH = "$DIR_ROOT/lib";
$CONF_PATH = "$DIR_ROOT/conf";

ini_set("include_path", "/home/joshutt/php:$SCRIPT_PATH:$CONF_PATH" . ini_get("include_path"));
$ini = parse_ini_file("$CONF_PATH/wmffl.conf");
#$ini = parse_ini_file("wmffl.conf");

#require_once "PEAR.php";
require_once "DB/DataObject.php";

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
#$config = parse_ini_file("$SCRIPT_PATH/dataobjects.ini", TRUE);
$config = parse_ini_file("$CONF_PATH/wmffl.conf", TRUE);
$options = $config['DB_DataObject'];
$debug = 5;

error_reporting(E_ALL & ~E_DEPRECATED);

#DB_DataObject::debugLevel($debug);

