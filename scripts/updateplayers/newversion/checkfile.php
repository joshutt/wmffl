<?
require_once "setup.php";

$master = file_get_contents("../data/mast2007.txt");
$previous = file_get_contents("lastrun");
//print($master);

$lines = split("\n", $master);

foreach ($lines as $line) {
    //print $line."\n";
    $sub = split(",", $line);
    //print $sub[0];
    if ($sub[0] == "date") {
        //print $sub[1]."\n";
        $val = $sub[1];
        break;
    }
}

if (!$previous || $previous!=$val) {
    //print "none\n";
    $writefile = fopen("lastrun", "w");
    fwrite($writefile, $sub[1]);
    fclose($writefile);
    system.exit(1);
}
//print "match\n";
system.exit(0);


?>
