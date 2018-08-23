<?php
require_once "utils/start.php";

$query = "select u.Name, t.name, o.primary, c.value, if(c.value > DATE_SUB(now(), INTERVAL 1 MINUTE), 'In', 'Out') as 'in', c2.value
from owners o
join user u on o.userid=u.UserID
join teamnames t on o.teamid=t.teamid and t.season=o.season
left join config c on c.key=concat('draft.login.', u.userid)
left join config c2 on c2.key=concat('draft.team.', t.teamid)
where o.season=2017
order by t.name";

$results = mysql_query($query) or die("Unable to do query: ".mysql_error());
$returnArray = array();
while ($row = mysql_fetch_array($results)) {
    array_push($returnArray, $row);
}

header("Content-type: text/x-json");
print json_encode($returnArray);
?>
