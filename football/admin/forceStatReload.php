<?
require_once "utils/setup.php";
$host = $config["Paths"]["scriptsPath"];
exec("$host/livescore/livescore.sh", $outArr);

$output = "";
foreach($outArr as $line) {
    $output .= $line."\n";
}
print $output;

file_put_contents("$host/logs/livelog", $output, FILE_APPEND);
