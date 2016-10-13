<?
include_once $DOCUMENT_ROOT."/base/conn.php";

include $DOCUMENT_ROOT."/base/menu.php";

$sql = "SELECT p.playerid as 'ID', CONCAT(p.firstname,' ',p.lastname) as 'Name', IFNULL(t.name, '-') as 'Team', p.nflteam as 'NFL', sum(ps.pts) as 'Pts'
FROM players p, playerscores ps
LEFT JOIN roster r ON p.playerid=r.playerid AND  r.dateoff is null
LEFT JOIN team t ON t.teamid=r.teamid
WHERE p.playerid=ps.playerid
AND ps.season=2004
AND p.position='RB'
AND ps.week<=14
AND p.status='A'
GROUP BY p.playerid
ORDER BY `Pts` DESC";
?>

<style>
font,th,td,p { font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size: 10pt;}
a:link,a:active,a:visited {color: #006699; text-decoration: none; font-weight: bold}
a:hover                 {color: #DD6900; text-decoration: underline}
th {background-color: #006699; text-align: center; 
    color: #ffa34f; padding: 0px 8px 0px 8px}
</style>

<?
$results = mysql_query($sql) or die("MySQL Error: ".mysql_error());
print "<table>";
print "<tr><th>Name</th><th>Team</th><th>NFL</th><th>Pts</th></tr>";
$odd = true;
while($player = mysql_fetch_array($results)) {
   if ($odd) $color="#f0f0f0"; else $color="#e0e0e0";
   $odd = !$odd;
   print "<tr bgcolor=\"$color\">";
   print "<td><a href=\"profile.php?id=${player["ID"]}\">${player["Name"]}</a></td><td>${player["Team"]}</td>";
   print "<td>${player["NFL"]}</td><td>${player["Pts"]}</td></tr>";
}
print "</table>";

include $DOCUMENT_ROOT."/base/footer.html";
?>
