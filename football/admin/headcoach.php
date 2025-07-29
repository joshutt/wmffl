<?php
/**
 * @var $conn mysqli
 */
include 'check.inc.php';
// establish connection
require 'base/conn.php';

$teamsql = 'SELECT t.teamid, t.name FROM team t ORDER BY t.name';
$teamResults = mysqli_query($conn, $teamsql);

$coachsql = <<<EOD
SELECT p.playerid, CONCAT(p.firstname, ' ', p.lastname) as 'name', p.team, 
t.name as 'wmfflteam'
FROM newplayers p 
left join roster r on p.playerid=r.playerid and r.dateoff is null
left join team t on r.teamid=t.teamid
WHERE p.pos='HC' and (p.team<>'' or t.name is not null) and p.active=1
ORDER BY p.lastname
EOD;

$results = mysqli_query($conn, $coachsql);

?>

<h3>(Change Head Coach)</h3>
<form action="headcoachprocess.php">
<select name="team">

<?php
while ($team = mysqli_fetch_array($teamResults)) {
    print "<option value=\"{$team['teamid']}\">{$team['name']}</option>";
}
?>
</select>

<table>
    <?php
while ($coach = mysqli_fetch_array($results)) {
    print <<<EOD
<tr>
    <td><input type="radio" name="player" value="{$coach['playerid']}"/></td>
    <td>{$coach['name']}</td>
    <td>{$coach['team']}</td>
    <td>{$coach['wmfflteam']}</td>
</tr>
EOD;
}
?>
</table>

    <input type="submit" value="Submit">
</form>
<a href="index">Return to Admin page</a>
