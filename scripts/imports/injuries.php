<?
//$conn = mysql_connect('localhost', 'joshutt_misc', 'swwrbo');
//mysql_select_db('joshutt_misc');

$conn = mysql_connect('localhost', 'joshutt_footbal', 'wmaccess');
mysql_select_db('joshutt_oldwmffl');

$week = $_REQUEST['week'];
#$season = 2011;

// Get the current week
if (isset($week)) {
    $sql = "select week, max(season) from weekmap where week=$week";
} else {
    $sql = "select week, season from weekmap where startDate < now() and endDate > now()";
}
$results = mysql_query($sql) or die ("Unable to get this week: ".mysql_error());

list($week, $season) = mysql_fetch_array($results);

$request_url = "http://www.myfantasyleague.com/$season/export?TYPE=injuries&JSON=1&W=$week";
$json = json_decode(file_get_contents($request_url));
#print_r($json);
#exit();
#$xml = simplexml_load_file($request_url) or die("Feed not loading");


// Display Output
/*
echo "<pre>";
var_dump($xml);
echo "</pre>";
 */

//print "<pre>";

$injuries = $json->injuries;
//print_r($injuries);
foreach ($injuries->injury as $injuries) {
    //print_r($injuries);

    $playerid = $injuries->id;
    $status = $injuries->status;
    $details = mysql_real_escape_string(trim($injuries->details));
    $statShort = substr($status, 0, 1);

    //print "$playerid - $status - $details - $statShort<br/>";
    $query = "select playerid, $season, $week, '$statShort', '$details' from newplayers where flmid=$playerid";
    //print $query;
    //print "\n";
    //print "<br/>";

    $fullQuery = "REPLACE into injuries $query";
    //$fullQuery = "Insert into injuries $query";
    mysql_query($fullQuery) or die("Unable to insert: ".mysql_error());
}

//print "</pre>";
?>
