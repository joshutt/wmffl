<?
// establish connection
print  $DOCUMENT_ROOT."/../base/conn.php";
require $DOCUMENT_ROOT."/../base/conn.php";

$teamsql = "SELECT t.teamid, t.name FROM team t ORDER BY t.name";
$teamResults = mysql_query($teamsql);

$coachsql = <<<EOD
SELECT p.playerid, CONCAT(p.firstname, ' ', p.lastname) as 'name', p.team, 
t.name as 'wmfflteam'
FROM newplayers p 
left join roster r on p.playerid=r.playerid and r.dateoff is null
left join team t on r.teamid=t.teamid
WHERE p.pos='HC' and (p.team<>'' or t.name is not null) and p.active=1
ORDER BY p.lastname
EOD;

$results = mysql_query($coachsql);
print "<form action=\"headcoachprocess.php\">";
print "<table>";

print "<tr><td colspan=\"4\" align=\"center\">";
print "<select name=\"team\">";
while ($team = mysql_fetch_array($teamResults)) {
    print "<option value=\"{$team["teamid"]}\">{$team["name"]}</option>";
}
print "</select>";
print "</td></tr>";


while ($coach = mysql_fetch_array($results)) {
    print <<<EOD
<tr>
    <td><input type="radio" name="player" value="{$coach["playerid"]}"/></td>
    <td>{$coach["name"]}</td>
    <td>{$coach["team"]}</td>
    <td>{$coach["wmfflteam"]}</td>
</tr>
EOD;
}
?>

<tr><td colspan="4" align="center"><input type="submit"></td></td>
</table>
</form>
