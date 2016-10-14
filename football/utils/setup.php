<?
$SCRIPT_PATH = "/home/joshutt/git/lib";

ini_set("include_path", "/home/joshutt/php:$SCRIPT_PATH:".ini_get("include_path"));

require_once "PEAR.php";
require_once "DB/DataObject.php";

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$config = parse_ini_file("$SCRIPT_PATH/dataobjects.ini", TRUE);
$options = $config['DB_DataObject'];

DB_DataObject::debugLevel($debug);

?>
