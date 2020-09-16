<?
#print "start<br/>";
#require_once "utils/start.php";

#$request_url = "http://football.myfantasyleague.com/2008/export?TYPE=players&L=&W=";
#$xml = simplexml_load_file($request_url) or die("Feed not loading");
#print "loaded XML<br/>";

#exec("/home/joshutt/git/scripts/test.sh");
#exec("/home/joshutt/git/scripts/livescore/livescore.sh");
$host = "/home/joshutt/git/scripts";
#exec("$host/livescore/livescore.sh >> $host/logs/livelog");
exec("$host/livescore/livescore.sh", $outArr);

#print exec("ls -l", $outArr);

$output = "";
foreach($outArr as $line) {
    $output .= $line."\n";
}
print $output;

file_put_contents("$host/logs/livelog", $output, FILE_APPEND);
