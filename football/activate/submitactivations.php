<?
$javascriptList = array("/base/js/activations.js");
$cssList = array("/base/css/activate.css");
//$cssList = array("/base/css/w3.css", "/base/css/activate.css");


require_once "utils/start.php";
require_once "base/conn.php";
require_once "login/loginglob.php";


//print "Set";
$season=$currentSeason;

if (!isset($week)) {
    $week = $currentWeek;
}

if (isset($_REQUEST["week"])) {
    $week = $_REQUEST["week"];
}


//$week = 7;
$teamid=$teamnum;
$currentTime = time();
//$currentTime = 1191861900;
//$currentTime =1220926000 ;

//print "Read";
$sql = <<<EOD

SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, n.nflteamid, a.playerid as 'activeId', g.kickoff, g.homeTeam, g.roadTeam, p.playerid, i.status, i.details
FROM newplayers p
JOIN roster r ON p.playerid=r.playerid AND r.dateoff is null
LEFT JOIN nflrosters n ON n.playerid=r.playerid and n.dateoff is null
LEFT JOIN revisedactivations a ON a.season=$season AND a.week=$week AND p.playerid=a.playerid AND a.teamid=r.teamid
LEFT JOIN nflgames g ON g.season=$season AND g.week=$week AND n.nflteamid in (g.homeTeam, g.roadTeam)
LEFT JOIN injuries i ON i.playerid=r.playerid and i.season=g.season AND i.week=g.week
WHERE r.teamid=$teamid 
ORDER BY p.pos, p.lastname

EOD;


// Gets the list of players that joined the team after activations due for week 14
$noActivateSql = <<<EOD

SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, p.playerid
FROM newplayers p
JOIN roster r1 ON p.playerid=r1.playerid AND r1.dateoff is null
JOIN roster r2 ON p.playerid=r2.playerid
JOIN weekmap w ON w.season=$season AND w.week=14 AND r2.dateoff>w.ActivationDue
WHERE r1.teamid=$teamid and r1.teamid<>r2.teamid
ORDER BY p.pos, p.lastname

EOD;

$actingHCsql = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, n.nflteamid, a.playerid as 'activeId', g.kickoff, g.homeTeam, g.roadTeam, p.playerid
FROM newplayers p
LEFT JOIN roster r on p.playerid=r.playerid and r.dateoff is null
LEFT JOIN nflrosters n ON n.playerid=p.playerid and n.dateoff is null
LEFT JOIN nflgames g ON g.season=$season AND g.week=$week AND n.nflteamid in (g.homeTeam, g.roadTeam)
LEFT JOIN revisedactivations a ON a.season=g.season AND a.week=g.week and p.playerid=a.playerid
WHERE p.pos='HC' AND r.playerid is null AND n.playerid is not null and g.kickoff>DATE_ADD(now(), INTERVAL 30 MINUTE)
AND (a.playerid is null or a.teamid=$teamid)
ORDER BY p.lastname
EOD;


$opponentRoster = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, p.playerid, n.nflteamid
FROM schedule s
  JOIN weekmap wm on s.Season=wm.Season and s.Week=wm.Week
  JOIN roster r on r.dateoff is null and r.TeamID = if(s.TeamA=$teamid, s.teamb, s.TeamA)
  JOIN newplayers p ON r.PlayerID=p.playerid
  LEFT JOIN nflrosters n ON n.playerid=p.playerid and n.dateoff is null
WHERE s.Season=$season and s.Week=$week and (s.TeamA=$teamid or s.TeamB=$teamid)
ORDER BY p.pos, p.lastname, p.firstname
EOD;



$weekSql = "SELECT week, weekname FROM weekmap WHERE Season=$season AND EndDate>now()";

$weekResults = mysql_query($weekSql) or die("Unable to get Weeks: ".mysql_error());

$weekList = "";
while ($theWeek = mysql_fetch_assoc($weekResults)) {
    $checked = "";
    if ($week == $theWeek['week']) {
        $checked = "selected=\"true\"";
    }
    $weekList .= "<option value=\"{$theWeek['week']}\" $checked>{$theWeek['weekname']}</option>"; 
}



//print $sql;

$title = "Submit Activations";
include "$DOCUMENT_ROOT/base/menu.php";

$actingHC = false;
if ($isin) {

    $results = mysql_query($sql) or die("Ug: ".mysql_error());

    $starters = array();
    $reserve = array();

    #putenv("TZ=US/Eastern");
    $maxDate = 0;
    //print_r($_REQUEST);
    $reserveCount = 0;
    $reserveIds = array();
    $gpOption = "<option>None</option>";
    while ($rowSet = mysql_fetch_assoc($results)) {
        #print_r($rowSet);
        #print "<br/>";

        $player = array();
        $player["name"] = $rowSet["name"];
        $player["pos"] = $rowSet["pos"];
        $player["nfl"] = $rowSet["nflteamid"];
        $player["playerid"] = $rowSet["playerid"];
        $player["injuryStatus"] = $rowSet["status"];
        $player["injuryDetail"] = $rowSet["details"];
        $gpOption .= "<option value=\"".$player["playerid"]."\">".$player["name"]." (".$player["pos"]."-".$player["nfl"].")</option>";

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
   # print "$deadLine - $currentTime<br/>";
        if ($rowSet['kickoff'] == "") {
            $deadLine = 0;
        } else {
            $deadLine = strtotime($rowSet['kickoff']) - 30*60;
        }
        if ($deadLine > $maxDate) {
            $maxDate = $deadLine;
        }

    #print $rowSet['kickoff'] ." - $deadLine - ".strtotime($rowSet['kickoff'])." - $currentTime<br/>";
        if ($currentTime > $deadLine && $deadLine>0) {
            $player["lock"] = true;
        } else {
            $player["lock"] = false;
        }


        $old = error_reporting(!E_WARNING);
        $posActive =  $_REQUEST[$player["pos"]];
        $checked = array_search($player["playerid"], $posActive);
        error_reporting($old);
        if ($checked == false && $rowSet["activeId"] == null) {
            $reserve[] = $player;
            $reserveIds[$reserveCount++] = $player["playerid"];
        } else {
            $starters[] = $player;
        }

        if ($player["pos"] == "HC" && $deadLine == 0) {
            $actingHC = true;
        }
    }

    $allLock = false;
    if ($currentTime > $maxDate) {
        $allLock = true;

    }


    $noActiveResults =  mysql_query($noActivateSql) or die("Die on No activate: ".mysql_error());
    while ($rowSet = mysql_fetch_assoc($noActiveResults)) {
        $key = array_search($rowSet["playerid"], $reserveIds);
        if ($key !== FALSE) {
            $player = $reserve[$key];
            $player["lock"] = true;
            $reserve[$key] = $player;
        }
    }

    $oppGPOption = "<option>None</option>";
    $oppRosterResults = mysql_query($opponentRoster) or die("Die on opponent roster: ".mysql_error());
    while ($rowSet = mysql_fetch_assoc($oppRosterResults)) {
        $player = array();
        $player["name"] = $rowSet["name"];
        $player["pos"] = $rowSet["pos"];
        $player["nfl"] = $rowSet["nflteamid"];
        $player["playerid"] = $rowSet["playerid"];
        $oppGPOption .= "<option value=\"".$player["playerid"]."\">".$player["name"]." (".$player["pos"]."-".$player["nfl"].") </option>";
    }
}

if ($actingHC) {
    $HCResults = mysql_query($actingHCsql) or die("Unable to get active HC: ".mysql_error());
    $hcArray = array();
    while ($rowSet = mysql_fetch_assoc($HCResults)) {
        $player = array();
        $player["name"] = $rowSet["name"];
        $player["pos"] = $rowSet["pos"];
        $player["nfl"] = $rowSet["nflteamid"];
        $player["playerid"] = $rowSet["playerid"];

        if ($rowSet["activeId"] != null) {
            $player["activeId"] = $rowSet["activeId"];
        }

        if ($rowSet["nflteamid"] == "") {
            $player["opp"] = "";
        } else if ($rowSet["kickoff"] == null) {
            $player["opp"] = "Bye";
        } else if ($rowSet["nflteamid"] == $rowSet["homeTeam"]) {
            $player["opp"] = "vs ".$rowSet["roadTeam"];
        } else if ($rowSet["nflteamid"] == $rowSet["roadTeam"]) {
            $player["opp"] = "@ ".$rowSet["homeTeam"];
        }

        array_push($hcArray, $player);
    }
}



?>

<h1 align=Center>Activations</H1>
<hr size = "1">
<table align=Center width=100% border=0>
    <tr>
<td width=33%><a href="activations.php"><img src="../images/football.jpg" border=0>Current Activations</a></td>
<td width=34%></td>
<td width=33%><a href="#Submit"><img src="../images/football.jpg" border=0>Submit Activations</a></td>
</tr></table>

<hr size = "1">
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

<table align="center" id="subAct">

<tr><td colspan="5" align="center">Week: <select name="week" onChange="swapActivations(this);"><? print $weekList; ?></select></td></tr>
<tr><th colspan="5">Starters</th></tr>


<?
if ($actingHC) {
    print "<tr>";
    print "<td><input name=\"actHC\" value=\"on\" type=\"checkbox\" checked=\"true\" /></td>";
    print "<td>{$player["pos"]}</td><td colspan=\"3\"><select name=\"actHCid\">";
    foreach ($hcArray as $hc) {
        if ($hc["activeId"] == null) {
            $checked = "";
        } else {
            $checked = "selected=\"TRUE\"";
        }
        print "<option value=\"{$hc["playerid"]}\" $checked>{$hc["name"]} - {$hc["nfl"]} {$hc["opp"]}</option>";
    }
    print "</select></td>";
    print "</tr>";
}

foreach ($starters as $player) {
    $lock = $player["lock"];
    if ($allLock) {
        $lock = true;
    }

    $injuryLine = "";
    if ($player["injuryStatus"] != "") {
        switch ($player["injuryStatus"]) {
            case 'P': $iWord = "Probable: ".$player["injuryDetail"]; break;
            case 'D': $iWord = "Doubtful: ".$player["injuryDetail"]; break;
            case 'Q': $iWord = "Questionable: ".$player["injuryDetail"]; break;
            case 'O': $iWord = "Out: ".$player["injuryDetail"]; break;
            case 'S': $iWord = "Suspension"; break;
        }
        $injuryLine = "<span class=\"PQDO\" title=\"$iWord\">{$player["injuryStatus"]}</span>";
    }

    print "<tr>";
    if ($lock) { ?>
        <td>
            <input type="hidden" name="<?= $player["pos"]?>[]" value="<?=$player["playerid"]?>"/>
            <img src="/images/lock-clipart2.gif" height="16" width="16" align="right"/>
            <?= $injuryLine ?>
        </td>
<?php } else { ?>
        <td>
            <input name="<?= $player["pos"] ?>[]" value="<?= $player["playerid"] ?>" type="checkbox" checked="true"/>
            <?= $injuryLine ?>
        </td>
<?php } ?>
    <td><?= $player["pos"] ?> </td>
    <td><?= $player["name"] ?></td>
    <td><?= $player["nfl"] ?></td>
    <td><?= $player["opp"] ?></td>
    </tr>
<?php } ?>

<tr><td>&nbsp;</td></tr>
<tr><th colspan="5">Reserves</th></tr>

<?
foreach ($reserve as $player) {
    $lock = $player["lock"];
    if ($allLock) {
        $lock = true;
    }

    $injuryLine = "";
    if ($player["injuryStatus"] != "") {
        switch ($player["injuryStatus"]) {
            case 'P': $iWord = "Probable: ".$player["injuryDetail"]; break;
            case 'D': $iWord = "Doubtful: ".$player["injuryDetail"]; break;
            case 'Q': $iWord = "Questionable: ".$player["injuryDetail"]; break;
            case 'O': $iWord = "Out: ".$player["injuryDetail"]; break;
        }
        $injuryLine = "<span class=\"PQDO\" title=\"$iWord\">{$player["injuryStatus"]}</span>";
    }

    print "<tr>";
    if ($lock) { ?>
        <td>
            <img src="/images/lock-clipart2.gif" height="16" width="16" align="left"/>
            <?= $injuryLine ?>
        </td>
<?php } else { ?>
        <td>
            <input name="<?= $player["pos"] ?>[]" type="checkbox" value="<?=$player["playerid"]?>" />
            <?= $injuryLine ?>
        </td>
<?php } ?>
    <td><?=$player["pos"]?></td>
    <td><?=$player["name"]?></td>
    <td><?=$player["nfl"]?></td>
    <td><?=$player["opp"]?></td>
    </tr>
<?php } ?>

<tr><td>&nbsp;</td></tr>

    <tr>
        <tr><th colspan="5">Game Plan</th></tr>
    <tr>
        <td colspan="2">My Team:</td>
        <td colspan="3">
            <select name="myGP"><?= $gpOption ?></select>
        </td>
    </tr>
    <tr>
        <td colspan="2">Their Team:</td>
        <td colspan="3">
            <select name="oppGP"><?= $oppGPOption ?></select>
        </td>
    </tr>

    </tr>

<tr><td>&nbsp;</td></tr>
<tr><td colspan="5" align="center"><input type="submit" value="Submit Activations"/></td></tr>
</table>
<input type="hidden" name="season" value="<?= $season; ?>"/>
</form>

<?
	} else {
?>

<CENTER><B>You must be logged in to submit activations</B></CENTER>

<? } ?>
<? include "$DOCUMENT_ROOT/base/footer.html";
?>
