<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (isset($_REQUEST['last'])) {
    $last = $_REQUEST['last'];
} else {
    $last = 0;
}

$sql = <<<EOD

SELECT c.messageId, c.userid, c.message, c.time, u.name
FROM chat c
LEFT JOIN user u ON c.userid=u.userid
WHERE c.time > '2009-08-03'
AND c.messageId > $last
ORDER BY c.time DESC
LIMIT 10

EOD;

$results = mysql_query($sql) or die("Error: ".mysql_error());
$body = "<table class=\"draft_picks_header report\" cellspacing=\"1\" align=\"center\" id=\"chat\">";
$body .= "<tbody><tr><th class=\"byName\">By</th>";
$body .= "<th class=\"message\">Message</th></tr>";
$team = "";
$first = true;
$maxPick = 0;
$count = 0;
while($row = mysql_fetch_assoc($results)) {

    if ($count % 2) {
        $class = "oddtablerow";
    } else {
        $class = "eventablerow";
    }

    if ($row["userid"] == 0) {
        $extraCss = "league";
    } else {
        $extraCss = "";
    }

    $count = $count+1;
    $body .= "<tr id=\"{$row["messageId"]}\" class=\"$class\">";
    $body .= "<td class=\"byName\">{$row["name"]}</td><td class=\"message $extraCss\">{$row["message"]}</td></tr>";
    
}

header("Content-type: text/html");

$xmlOutput .= $body;

print $xmlOutput;

?>
</tbody>
</table>
