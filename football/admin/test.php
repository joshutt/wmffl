<?
print "start<br/>";
require_once "$DOCUMENT_ROOT/utils/start.php";

$request_url = "http://football.myfantasyleague.com/2008/export?TYPE=players&L=&W=";
$xml = simplexml_load_file($request_url) or die("Feed not loading");
print "loaded XML<br/>";
?>
