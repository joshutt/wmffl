<?php
$javascriptList = array("/base/js/activations.js");
$cssList = array("/base/css/activate.css");
//$cssList = array("/base/css/w3.css", "/base/css/activate.css");


require_once "utils/start.php";
require_once "utils/injuryUtils.php";
//require_once "login/loginglob.php";

/**
 * @param $player
 * @return string[]
 */
function getInjuryLine($player): string
{
    $injuryLine = "";
    $status = "";
    // Get injury status from injury table
    if ($player["injuryStatus"] != "") {
        $status = shortenInjury($player["injuryStatus"]);
    }

    // If on the IR then make status IR
    if ($player["ir"] === "1") {
        $status = "IR";
    }

    // Print out the injury Line
    if ($status !== "") {
        $injuryLine = "<span class=\"PQDO\" title=\"{$player["injuryDetail"]}\">($status)</span>";
    }

    return $injuryLine;
}

//print "Set";
$season = $currentSeason;

if (!isset($week)) {
    $week = $currentWeek;
}

if (isset($_REQUEST["week"])) {
    $week = $_REQUEST["week"];
}


//$week = 7;
$teamid = $teamnum;
$currentTime = time();
//$currentTime = 1191861900;
//$currentTime =1220926000 ;

//print "Read";
$sql = <<<EOD

SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, n.nflteamid, a.playerid as 'activeId', g.kickoff, 
g.homeTeam, g.roadTeam, p.playerid, i.status, i.details, ir.current as 'ir'
FROM newplayers p
JOIN roster r ON p.playerid=r.playerid AND r.dateoff is null
LEFT JOIN nflrosters n ON n.playerid=r.playerid and n.dateoff is null
LEFT JOIN revisedactivations a ON a.season=$season AND a.week=$week AND p.playerid=a.playerid AND a.teamid=r.teamid
LEFT JOIN nflgames g ON g.season=$season AND g.week=$week AND n.nflteamid in (g.homeTeam, g.roadTeam)
LEFT JOIN newinjuries i ON i.playerid=r.playerid and i.season=g.season AND i.week=g.week
LEFT JOIN ir on ir.playerid=p.playerid and ir.dateoff is null
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
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, n.nflteamid, a.playerid as 'activeId', CONVERT_TZ(g.kickoff, 'SYSTEM', 'GMT') as 'kickoff', g.homeTeam, g.roadTeam, p.playerid
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

$weekResults = mysqli_query($conn, $weekSql) or die("Unable to get Weeks: " . mysqli_error($conn));

$weekList = "";
while ($theWeek = mysqli_fetch_assoc($weekResults)) {
    $checked = "";
    if ($week == $theWeek['week']) {
        $checked = "selected=\"true\"";
    }
    $weekList .= "<option value=\"{$theWeek['week']}\" $checked>{$theWeek['weekname']}</option>";
}


$title = "Submit Activations";
include "base/menu.php";

$actingHC = false;
if (isset($isin) && $isin) {

    $results = mysqli_query($conn, $sql) or die("Ug: " . mysqli_error($conn));

    $starters = array();
    $reserve = array();

    putenv("TZ=US/Eastern");
    $maxDate = 0;
    //print_r($_REQUEST);
    $reserveCount = 0;
    $reserveIds = array();
    while ($rowSet = mysqli_fetch_assoc($results)) {

        $player = array();
        $player["name"] = $rowSet["name"];
        $player["pos"] = $rowSet["pos"];
        $player["nfl"] = $rowSet["nflteamid"];
        $player["playerid"] = $rowSet["playerid"];
        $player["injuryStatus"] = $rowSet["status"];
        $player["injuryDetail"] = $rowSet["details"];
        $player["ir"] = $rowSet["ir"];

        if ($rowSet["nflteamid"] == "") {
            $player["opp"] = "";
        } else if ($rowSet["kickoff"] == null) {
            $player["opp"] = "Bye";
        } else if ($rowSet["nflteamid"] == $rowSet["homeTeam"]) {
            $player["opp"] = "vs " . $rowSet["roadTeam"];
        } else if ($rowSet["nflteamid"] == $rowSet["roadTeam"]) {
            $player["opp"] = "@ " . $rowSet["homeTeam"];
        }

        $format = '%Y-%m-%d %H:%M:%S';
        $realTime = strtotime($rowSet['kickoff']) - 2 * 60 * 60;
        # print "$deadLine - $currentTime<br/>";
        if ($rowSet['kickoff'] == "") {
            $deadLine = 0;
        } else {
            $deadLine = strtotime($rowSet['kickoff']) - 30 * 60;
        }
        if ($deadLine > $maxDate) {
            $maxDate = $deadLine;
        }

        # print $rowSet['kickoff'] ." - $deadLine - ".strtotime($rowSet['kickoff'])." - $currentTime<br/>";
        if ($currentTime > $deadLine && $deadLine > 0) {
            $player["lock"] = true;
        } else {
            $player["lock"] = false;
        }


        $old = error_reporting(!E_WARNING);
        $posActive = $_REQUEST[$player["pos"]];
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


    $noActiveResults = mysqli_query($conn, $noActivateSql) or die("Die on No activate: " . mysqli_error($conn));
    while ($rowSet = mysqli_fetch_assoc($noActiveResults)) {
        $key = array_search($rowSet["playerid"], $reserveIds);
        if ($key !== FALSE) {
            $player = $reserve[$key];
            $player["lock"] = true;
            $reserve[$key] = $player;
        }
    }

    $oppRosterResults = mysqli_query($conn, $opponentRoster) or die("Die on opponent roster: " . mysqli_error($conn));
    while ($rowSet = mysqli_fetch_assoc($oppRosterResults)) {
        $player = array();
        $player["name"] = $rowSet["name"];
        $player["pos"] = $rowSet["pos"];
        $player["nfl"] = $rowSet["nflteamid"];
        $player["playerid"] = $rowSet["playerid"];
    }
}

if ($actingHC) {
    $HCResults = mysqli_query($conn, $actingHCsql) or die("Unable to get active HC: " . mysqli_error($conn));
    $hcArray = array();
    while ($rowSet = mysqli_fetch_assoc($HCResults)) {
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
            $player["opp"] = "vs " . $rowSet["roadTeam"];
        } else if ($rowSet["nflteamid"] == $rowSet["roadTeam"]) {
            $player["opp"] = "@ " . $rowSet["homeTeam"];
        }

        array_push($hcArray, $player);
    }
}


?>

    <h1 align=Center>Activations</H1>
    <hr size="1">
    <table align=Center width=100% border=0>
        <tr>
            <td width=33%><a href="activations.php"><img src="../images/football.jpg" border=0>Current Activations</a>
            </td>
            <td width=34%></td>
            <td width=33%><a href="#Submit"><img src="../images/football.jpg" border=0>Submit Activations</a></td>
        </tr>
    </table>

    <hr size="1">
<?

if ($isin) {

    ?>
    <a name="Submit"/>

    <form action="processActivations.php" method="POST" name="activeForm">

        <?
        if (isset($activeMessage) && $activeMessage != "") {
            print "<div class=\"alert\">$activeMessage</div>";
        }
        ?>

        <table align="center" id="subAct">

            <tr>
                <td colspan="5" class="text-center">Week: <select name="week"
                                                                  onChange="swapActivations(this);"><? print $weekList; ?></select>
                </td>
            </tr>
            <tr>
                <th colspan="6" class="text-center">Starters</th>
            </tr>


            <?
            if ($actingHC) {
                print "<tr>";
                print "<td><input name=\"actHC\" value=\"on\" type=\"checkbox\" checked=\"true\" /></td>";
                print "<td>{$player["pos"]}</td><td colspan=\"3\"><select name=\"actHCid\">";
                foreach ($hcArray as $hc) {
                    if (!array_key_exists("activeId", $hc) || $hc["activeId"] == null) {
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
                $injuryLine = getPQDOLine($player["injuryStatus"], $player["injuryDetail"], $player["ir"]);

                print "<tr>";
                if ($lock) { ?>
                    <td class="p-1 mx-2">
                        <input type="hidden" name="<?= $player["pos"] ?>[]" value="<?= $player["playerid"] ?>"/>
                        <img src="/images/lock-clipart2.gif" height="16" width="16" align="right"/>
                    </td>
                <?php } else { ?>
                    <td class="p-1 mx-2">
                        <input name="<?= $player["pos"] ?>[]" value="<?= $player["playerid"] ?>" type="checkbox"
                               checked="true"/>
                    </td>
                <?php } ?>
                <td class="p-1 mx-2"><?= $player["pos"] ?> </td>
                <td class="p-1 mx-2"><?= $player["name"] ?></td>
                <td class="p-1 mx-2"><?= $player["nfl"] ?></td>
                <td class="p-1 mx-2"><?= $player["opp"] ?></td>
                <td class="p-1 mx-2"><?= $injuryLine ?></td>
                </tr>
            <?php } ?>

            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th colspan="6" class="text-center">Reserves</th>
            </tr>

            <?
            foreach ($reserve as $player) {
                $lock = $player["lock"];
                if ($allLock) {
                    $lock = true;
                }
                $injuryLine = getPQDOLine($player["injuryStatus"], $player["injuryDetail"], $player["ir"]);

                print "<tr>";
                if ($lock) { ?>
                    <td class="p-1 mx-2">
                        <img src="/images/lock-clipart2.gif" height="16" width="16" align="left"/>
                    </td>
                <?php } else { ?>
                    <td class="p-1 mx-2">
                        <input name="<?= $player["pos"] ?>[]" type="checkbox" value="<?= $player["playerid"] ?>"/>
                    </td>
                <?php } ?>
                <td class="p-1 mx-2"><?= $player["pos"] ?></td>
                <td class="p-1 mx-2"><?= $player["name"] ?></td>
                <td class="p-1 mx-2"><?= $player["nfl"] ?></td>
                <td class="p-1 mx-2"><?= $player["opp"] ?></td>
                <td class="p-1 mx-2"><?= $injuryLine ?></td>
                </tr>
            <?php } ?>

            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="5" align="center"><input type="submit" value="Submit Activations"/></td>
            </tr>
        </table>
        <input type="hidden" name="season" value="<?= $season; ?>"/>
    </form>

    <?php
} else {
    ?>

    <CENTER><B>You must be logged in to submit activations</B></CENTER>

    <?php
}
include "base/footer.html";
