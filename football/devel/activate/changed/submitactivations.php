<? 
require_once "$DOCUMENT_ROOT/utils/start.php";
require_once "$DOCUMENT_ROOT/base/conn.php";
require_once "$DOCUMENT_ROOT/login/loginglob.php";

print "**$teamnum**";

//print "Set";
$season=2007;
$week = 5;
$teamid=2;
$currentTime = time();
$currentTime = 1191861900;
$currentTime = 1191866400;

//print "Read";
$sql = <<<EOD

SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, n.nflteamid, a.playerid as 'activeId', g.kickoff, g.homeTeam, g.roadTeam, p.playerid
FROM newplayers p
JOIN roster r ON p.playerid=r.playerid AND r.dateoff is null
LEFT JOIN nflrosters n ON n.playerid=r.playerid and n.dateoff is null
LEFT JOIN revisedactivations a ON a.season=$season AND a.week=$week AND p.playerid=a.playerid AND a.teamid=r.teamid
LEFT JOIN nflgames g ON g.season=$season AND g.week=$week AND n.nflteamid in (g.homeTeam, g.roadTeam)
WHERE r.teamid=$teamid 
ORDER BY p.pos, p.lastname

EOD;

//print $sql;

$title = "Submit Activations";
include "$DOCUMENT_ROOT/base/menu.php";


$results = mysql_query($sql) or die("Ug: ".mysql_error());

$starters = array();
$reserve = array();

putenv("TZ=US/Eastern");
$maxDate = 0;
//print_r($_REQUEST);
while ($rowSet = mysql_fetch_assoc($results)) {
    //print_r($rowSet);
    //print "<br/>";

    $player = array();
    $player["name"] = $rowSet["name"];
    $player["pos"] = $rowSet["pos"];
    $player["nfl"] = $rowSet["nflteamid"];
    $player["playerid"] = $rowSet["playerid"];

    if ($rowSet["nflteamid"] == "") {
        $player["opp"] = "";
    } else if ($rowSet["kickoff"] == null) {
        $player["opp"] = "Bye";
    } else if ($rowSet["nflteamid"] == $rowSet["homeTeam"]) {
        $player["opp"] = "vs ".$rowSet["roadTeam"];
    } else if ($rowSet["nflteamid"] == $rowSet["roadTeam"]) {
        $player["opp"] = "@ ".$rowSet["homeTeam"];
    }

    $format = '%Y-%m-%d %H:%M:%S';
    $realTime = strtotime($rowSet['kickoff']) - 2*60*60;
//    print $rowSet['kickoff'] ." - $realTime - ".strtotime($rowSet['kickoff'])." - ".time()."<br/>";
    $deadLine = strtotime($rowSet['kickoff']) - 30*60;
    if ($deadLine > $maxDate) {
        $maxDate = $deadLine;
    }

    if ($currentTime > $deadLine) {
        $player["lock"] = true;
    } else {
        $player["lock"] = false;
    }

    $posActive =  $_REQUEST[$player["pos"]];
    $checked = array_search($player["playerid"], $posActive);
    if ($checked === false && $rowSet["activeId"] == null) {
        array_push($reserve, $player);
    } else {
        array_push($starters, $player);
    }
}

$allLock = false;
if ($currentTime > $maxDate) {
    $allLock = true;

}
?>

<H1 ALIGN=Center>Activations</H1>
<HR size = "1">
<TABLE ALIGN=Center WIDTH=100% BORDER=0>
<TD WIDTH=33%><A HREF="activations.php"><IMG SRC="/images/football.jpg" BORDER=0>Current Activations</A></TD>
<TD WIDTH=34%></TD>
<TD WIDTH=33%><A HREF="#Submit"><IMG SRC="/images/football.jpg" BORDER=0>Submit Activations</A></TD>
</TR></TABLE>

<HR size = "1">
<?

if ($isin) {

?>
<a name="Submit"/>

<form action="processActivations.php" method="POST" name="activeForm">

<?
if ($activeMessage != "") {
    print "<div class=\"alert\">$activeMessage</div>";
}
?>

<table align="center">
<tr><th colspan="5">Starters</th></tr>


<?
foreach ($starters as $player) {
    $lock = $player["lock"];
    if ($allLock) {
        $lock = true;
    }

    print "<tr>";
    if ($lock) {
        print "<td>Locked<input type=\"hidden\" name=\"{$player["pos"]}[]\" value=\"{$player["playerid"]}\" /></td>";
    } else {
        print "<td><input name=\"{$player["pos"]}[]\" value=\"{$player["playerid"]}\" type=\"checkbox\" checked=\"true\" /></td>";
    }
    print "<td>{$player["pos"]}</td><td>{$player["name"]}</td><td>{$player["nfl"]}</td><td>{$player["opp"]}</td>";
    print "</tr>";
}
?>

<tr><td>&nbsp;</td></tr>
<tr><th colspan="5">Reserves</th></tr>

<?
foreach ($reserve as $player) {
    $lock = $player["lock"];
    if ($allLock) {
        $lock = true;
    }

    print "<tr>";
    if ($lock) {
        print "<td>Locked</td>";
    } else {
        print "<td><input name=\"{$player["pos"]}[]\" type=\"checkbox\" value=\"{$player["playerid"]}\" /></td>";
    }
    print "<td>{$player["pos"]}</td><td>{$player["name"]}</td><td>{$player["nfl"]}</td><td>{$player["opp"]}</td>";
    print "</tr>";
}
?>

<tr><td>&nbsp;</td></tr>
<tr><td colspan="5" align="center"><input type="submit" value="Submit Activations"/></td></tr>
</table>
<input type="hidden" name="season" value="<? print $season; ?>"/>
<input type="hidden" name="week" value="<? print $week; ?>"/>
</form>

<?
	} else {
?>

<CENTER><B>You must be logged in to submit activations</B></CENTER>

<? } ?>
<? include "$DOCUMENT_ROOT/base/footer.html";
?>
