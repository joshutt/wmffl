<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$sql = <<<EOD

SELECT c.messageId, c.userid, c.message, c.time, u.name
FROM chat c
JOIN user u ON c.userid=u.userid
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
    $count = $count+1;
    $body .= "<tr id=\"{$row["messageId"]}\" class=\"$class\">";
    $body .= "<td class=\"byName\">{$row["name"]}</td><td class=\"message\">{$row["message"]}</td></tr>";
    
}

header("Content-type: text/html");

$xmlOutput .= $body;

print $xmlOutput;

?>
</tbody>
</table>
