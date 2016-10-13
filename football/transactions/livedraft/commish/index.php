<?php
require_once "utils/start.php";

if (!$isin || $usernum!=2) {
?>

<h2>Please log in to use commish tools</h2>

<?
return -1;
}


?>

<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="commish.js" type="text/javascript"></script>
<link href="commish.css" type="text/css" rel="stylesheet" />
</head>

<body onload="setClock();">

<?php
$query = "select u.Name, t.name, o.primary, c.value, if(c.value > DATE_SUB(now(), INTERVAL 2 MINUTE), 'In', 'Out'), c2.value, t.teamid
from owners o
join user u on o.userid=u.UserID
join teamnames t on o.teamid=t.teamid and t.season=o.season
left join config c on c.key=concat('draft.login.', u.userid)
left join config c2 on c2.key=concat('draft.team.', t.teamid)
where o.season=2016
order by t.name";

$results = mysql_query($query) or die("Unable to do query: ".mysql_error());
?>

<div class="item">
<?php
print "<table id=\"logins\"><tr><th>Name</th><th>Team</th><th>In</th><th>Time</th><th>Auto Pick</th></tr>";
while ($row = mysql_fetch_array($results)) {
    if ($row[4] == "In") {
        $png = "green.png";
    } else {
        $png = "red.png";
    }
    //print "<tr><td>{$row[0]}</td><td>{$row[1]}</td><td><img src=\"$png\" height=\"30px\" width=\"30px\"/></td><td></td></tr>";
    print "<tr><td>{$row[0]}</td><td>{$row[1]}</td><td><img src=\"$png\" height=\"30px\" width=\"30px\"/></td><td></td>";
    print "<td><span class=\"autoPick\" onClick=\"autoPick({$row[6]})\">AUTO</span></td></tr>";
    //print "<tr><td>{$row[0]}</td><td>{$row[1]}</td><td>{$row[4]}</td></tr>";
}
print "</table>";
?>
</div>

<div class="item">
<h3><div id="clockStatus">Clock is </div></h3>
<div id="team"></div>
<div id="clock">5:00</div>
<div id="stClock" class="button" onclick="changeClock();">Start Clock</div>
<div id="undo" class="button" onclick="undoPick();">Undo Pick</div>
<div class="highlight">Total draft time: <div id="totalTime"/></div>
</div>


</body>
