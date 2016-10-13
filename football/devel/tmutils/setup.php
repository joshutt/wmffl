<?
#$SCRIPT_PATH = "/home/wmffl/scripts/pear";
$SCRIPT_PATH = "/home/joshutt/football/devel/lib";

ini_set("include_path", "/home/joshutt/php:$SCRIPT_PATH:".ini_get("include_path"));
#ini_set("include_path", "/home/wmffl/pear/lib/php:/home/wmffl/scripts/pear:".ini_get("include_path"));

require_once "PEAR.php";
require_once "DB/DataObject.php";

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$config = parse_ini_file("$SCRIPT_PATH/dataobjects.ini", TRUE);
$options = $config['DB_DataObject'];
#$debug=5;

DB_DataObject::debugLevel($debug);

?>
