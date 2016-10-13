<?

ini_set("include_path", "/home/wmffl/pear/lib/php:".ini_get("include_path"));

print ini_get("include_path");

require_once "PEAR.php";
require_once "DB/DataObject.php";


$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$options = array(
    'database'          => 'mysql://wmffl_user:wmaccess@localhost/wmffl_test',
    'schema_location'   => '/home/wmffl/scripts/pear/DataObjects',
    'class_location'    => '/home/wmffl/scripts/pear/DataObjects',
    'require_prefix'    => 'DataObjects/',
    'class_prefix'      => 'DataObjects_',

);
?>
