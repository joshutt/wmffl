<?
require_once "lib/Config.php";

$config = new Config($_SERVER['HTTP_configFile']);
ini_set("include_path", ini_get("include_path"));

require_once "PEAR.php";
require_once "DB/DataObject.php";

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$options = $config->getCategory("DB_DataObject");

if (isset($_REQUEST["debug"])) {
	$debug = $_REQUEST["debug"];
	#DB_DataObject::debugLevel(5);
	DB_DataObject::debugLevel($debug);
}

