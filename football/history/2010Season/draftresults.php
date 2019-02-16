<?
require_once "utils/start.php";

$season = 2010;
$dateSet = "'2010-08-29'";


if (isset($round) && $round != 'ALL') {
    $extra .= "and d.round=$round ";
}
if (isset($pick) && $pick != 'ALL') {
    $extra .= "and d.pick=$pick ";
}
if (isset($team) && $team != 'ALL') {
    $extra .= "and t.teamid='$team' ";
}
if (isset($pos) && $pos != 'ALL') {
    $extra .= "and p.pos='$pos' ";
}
if (isset($nfl) && $nfl != 'ALL') {
    $extra .= "and r.nflteamid='$nfl' ";
}


$sql = <<<EOD

SELECT d.round, d.pick, t.name as 'team', p.firstname, p.lastname, p.pos, r.nflteamid
FROM draftpicks d
JOIN teamnames t ON d.teamid=t.teamid and t.season=d.season
JOIN newplayers p on d.playerid=p.playerid
LEFT JOIN nflrosters r on r.playerid=p.playerid and dateon <= $dateSet
and (dateoff is null or dateoff >= $dateSet)
WHERE d.season=$season
$extra
ORDER by d.round, d.pick

EOD;


$uniqueTeam = <<<EOD
SELECT DISTINCT (teamid), name
FROM teamnames
WHERE season =$season
ORDER BY name
EOD;

$teamResult = mysql_query($uniqueTeam) or die("Unable to get teams: ".mysql_error());

$uniqueNfl = <<<EOD
SELECT DISTINCT (nflteamid)
FROM nflrosters
WHERE dateon <= $dateSet and (dateoff is null or dateoff >= $dateSet)
ORDER BY nflteamid
EOD;

$nflResult = mysql_query($uniqueNfl) or die("Unable to get teams: ".mysql_error());



$title = "2010 WMFFL Draft Results";
?>
<? include "base/menu.php"; ?>

<style>

.report {
    width: 100%;
    color: #660000;
}
.pick {
    width: 10%;
    text-align: center;
    font-size: 8pt;
    white-space: nowrap;
}

.round {
    width: 10%;
    text-align: center;
    font-size: 8pt;
    white-space: nowrap;
}
.franchise {
    width: 25%;
    font-size: 8pt;
    white-space: nowrap;
}
.selection {
    width: 35%;
    font-size: 8pt;
    white-space: nowrap;
}
.pos {
    width: 10%;
    font-size: 8pt;
    white-space: nowrap;
    text-align: center;
}
.nfl {
    width: 10%;
    font-size: 8pt;
    white-space: nowrap;
    text-align: center;
}
th {
    background-color: #660000;
    color: #e2a500;
    font-style: italic;
    font-weight: bold;
    font-size: 8pt;
}
body {
    font-family: Verdana;
    font-size: 8pt;
    background-color: #FFFFFF;
    border: 0px none;
}
</style>

<H1 Align=Center>Draft Results</H1>
<H5 ALIGN=Center><I>August 29, 2010</I></H5>
<HR size = "1">

<form method="post" action="draftresults.php">
Round: <select name="round"><option value="ALL">ALL</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select>
Pick: <select name="pick"><option value="ALL">ALL</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option></select>
Team: <select name="team"><option value="ALL">ALL</option><?

while($row = mysql_fetch_assoc($teamResult)) {
    print "<option value=\"{$row["teamid"]}\">{$row["name"]}</option>";
}

?></select>
Pos: <select name="pos"><option value="ALL">ALL</option><option value="QB">QB</option><option value="RB">RB</option><option value="WR">WR</option><option value="TE">TE</option><option value="K">K</option><option name="OL">OL</option><option name="DL">DL</option><option name="LB">LB</option><option name="DB">DB</option></select>
NFL: <select name="nfl"><option value="ALL">ALL</option>
<?
while($row = mysql_fetch_assoc($nflResult)) {
    print "<option value=\"{$row["nflteamid"]}\">{$row["nflteamid"]}</option>";
}
?></select>
<input type="submit" value="Submit" />
</form>



        <table class="report" cellspacing="1" align="center">
            <tbody>
                        <tr>
                            <th class="round">Rd</th>
                            <th class="pick">Pick</th>
                            <th class="franchise">Franchise</th>
                            <th class="selection">Selection</th>
                            <th class="pos">Pos</th>
                            <th class="nfl">NFL</th>
                        </tr>

<?
$results = mysql_query($sql) or die("Ug: ".mysql_error());
while ($row = mysql_fetch_assoc($results)) {

    print <<<EOD
                <tr id="pick_{$roundDist}_{$pickDist}">
                    <td class="round">{$row["round"]}</td>
                    <td class="pick">{$row["pick"]}</td>
                    <td class="franchise">{$row["team"]}</td>
                    <td class="selection">{$row["firstname"]} {$row["lastname"]}</td>
                    <td class="pos">{$row["pos"]}</td>
                    <td class="nfl">{$row["nflteamid"]}</td>
                </tr>
EOD;

}


?>

            </tbody>
        </table>


<? include "base/footer.html"; ?>
