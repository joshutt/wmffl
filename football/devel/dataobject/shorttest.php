<?
ini_set("include_path", "/home/wmffl/pear/lib/php:/home/wmffl/lib:".ini_get("include_path"));

require_once "PEAR.php";
require_once "DB/DataObject.php";

$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
$config = parse_ini_file('/home/wmffl/lib/dataobjects.ini', TRUE);
$options = $config['DB_DataObject'];

DB_DataObject::debugLevel($debug);


include_once "DataObjects/Team.php";

$team = new DataObjects_Team;
$team->active = 1;
$team->find();

while($team->fetch()) {
    print $team->Name."<br/>";
}
?>
