<?
require_once "$DOCUMENT_ROOT/utils/start.php";

#print "Pos: $position";
#print "Status: $status";
#print "Start: $startseason $startweek";
#print "End: $endseason $endweek";

if ($startseason == $endseason) {
    $dateWhere = "AND s.season=$startseason AND s.week BETWEEN $startweek AND $endweek ";
} else {
    $dateWhere = "AND ((s.season=$startseason AND s.week>=$startweek) ";
    $dateWhere .= "OR (s.season=$endseason AND s.week <= $endweek) ";
    $dateWhere .= "OR (s.season > $startseason AND s.season < $endseason) ";
}

$query = "SELECT *, sum(ps.pts) as 'pts', sum(ps.active) as 'ffpts'
FROM players p, playerscores ps, stats s
WHERE p.playerid=ps.playerid AND p.statid=s.statid
AND p.position='$position' AND ps.season=s.season
AND ps.week=s.week $dateWhere 
GROUP BY p.playerid 
ORDER BY `pts` DESC
LIMIT 10 ";

$results = mysql_query($query) or die("Dead: ".mysql_error());

$display = "<table width=\"100%\">";
while ($player = mysql_fetch_array($results)) {
    $display .= "<tr><td>${player['FirstName']} ${player['LastName']}</td>";
    $display .=  "<td>${player['NFLTeam']}</td><td>${player['pts']}</td>";
    $display .= "<td>${player['ffpts']}</td></tr>";
//    print_r($player);
}
$display .= "</table>";



$title = "Query";
?>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<form method="post">

<table>
<tr><td>

<b>Position:</b>
</td><td>
<select name="position">
    <option value="QB">Quarterback</option>
    <option value="RB">Runningback</option>
    <option value="WR">Wide Receiver</option>
    <option value="TE">Tight End</option>
    <option value="K">Kicker</option>
    <option value="OL">Offensive Line</option>
    <option value="DL">Defensive Line</option>
    <option value="LB">Linebacker</option>
    <option value="DB">Defensive Back</option>
    <option value="HC">Head Coach</option>
</select>
</td><td>

<b>Status:</b>
</td><td>
<select name="status">
    <option value="all">All Players</option>
    <option value="avail">Available Players</option>
    <option value="take">Taken Players</option>
</select>    
</td></tr>

<tr><td>
<b>Start:</b>
</td><td>
<select name="startseason">
    <option value="2005">2005</option>
    <option value="2004">2004</option>
    <option value="2003">2003</option>
    <option value="2002">2002</option>
</select>
<select name="startweek">
    <option value="1">Week 1</option>
    <option value="2">Week 2</option>
    <option value="3">Week 3</option>
    <option value="4">Week 4</option>
    <option value="5">Week 5</option>
    <option value="6">Week 6</option>
    <option value="7">Week 7</option>
    <option value="8">Week 8</option>
    <option value="9">Week 9</option>
    <option value="10">Week 10</option>
    <option value="11">Week 11</option>
    <option value="12">Week 12</option>
    <option value="13">Week 13</option>
    <option value="14">Week 14</option>
    <option value="15">Playoffs</option>
    <option value="16">Chamnpionship</option>
</select>
</td><td>

<b>End:</b>
</td><td>
<select name="endseason">
    <option value="2005">2005</option>
    <option value="2004">2004</option>
    <option value="2003">2003</option>
    <option value="2002">2002</option>
</select>
<select name="endweek">
    <option value="1">Week 1</option>
    <option value="2">Week 2</option>
    <option value="3">Week 3</option>
    <option value="4">Week 4</option>
    <option value="5">Week 5</option>
    <option value="6">Week 6</option>
    <option value="7">Week 7</option>
    <option value="8">Week 8</option>
    <option value="9">Week 9</option>
    <option value="10">Week 10</option>
    <option value="11">Week 11</option>
    <option value="12">Week 12</option>
    <option value="13">Week 13</option>
    <option value="14">Week 14</option>
    <option value="15">Playoffs</option>
    <option value="16">Chamnpionship</option>
</select>
</td></tr>

<tr><td colspan="5" align="center">
<input type="submit" value="submit"/>    
</td></tr>
</table>
    
</form>

<? if ($display != "") {
    print "<hr/>$display";
}
?>


<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
