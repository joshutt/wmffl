<?

$fd = fopen ("http://football118.fantasy.nfl.com/mp/statistics-live-twoteams-football?league=wmffl28&owner=88596&random=1968&players=Actives&match=1&last_scores=&last_match=1&kiosk=1&last_players=Actives", "r");
$contents = fread ($fd, 10000);
fclose ($fd);
# print($contents);

include("temp.php");

?>