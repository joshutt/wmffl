<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (!$isin) {
    include "submitactivations.php";
    exit();
}

$actID = $_REQUEST["actHC"];
$actHC = $_REQUEST["actHCid"];
$HC = $_REQUEST["HC"];
$QB = $_REQUEST["QB"];
$RB = $_REQUEST["RB"];
$WR = $_REQUEST["WR"];
$TE = $_REQUEST["TE"];
$K = $_REQUEST["K"];
$OL = $_REQUEST["OL"];
$DL = $_REQUEST["DL"];
$LB = $_REQUEST["LB"];
$DB = $_REQUEST["DB"];


$activeMessage = "";
if (sizeof($HC) != 1) {
    $activeMessage .= "You must activate exactly 1 HC<br/>";
}
if (sizeof($QB) != 1) {
    $activeMessage .= "You must activate exactly 1 QB<br/>";
}

if (sizeof($RB) < 1) {
    $activeMessage .= "You must activate at least 1 RB<br/>";
} else if (sizeof($RB) > 2) {
    $activeMessage .= "You can activate at most 2 RBs<br/>";
}

if (sizeof($WR) < 2) {
    $activeMessage .= "You must activate at least 2 WRs<br/>";
} else if (sizeof($WR) > 3) {
    $activeMessage .= "You can activate at most 3 WRs<br/>";
}

if (sizeof($TE) < 1) {
    $activeMessage .= "You must activate at least 1 TE<br/>";
} else if (sizeof($TE) > 2) {
    $activeMessage .= "You can activate at most 2 TEs<br/>";
}

if (sizeof($RB) + sizeof($WR) + sizeof($TE) != 5) {
    $activeMessage .= "You must activate 1 RB, 2 WR, 1 TE and 1 flex<br/>";
} 

if (sizeof($K) != 1) {
    $activeMessage .= "You must activate exactly 1 K<br/>";
}
if (sizeof($OL) != 1) {
    $activeMessage .= "You must activate exactly 1 OL<br/>";
}
if (sizeof($DL) != 2) {
    $activeMessage .= "You must activate exactly 2 DLs<br/>";
}
if (sizeof($LB) != 2) {
    $activeMessage .= "You must activate exactly 2 LBs<br/>";
}
if (sizeof($DB) != 2) {
    $activeMessage .= "You must activate exactly 2 DBs<br/>";
}


if ($activeMessage != "") {
    include "submitactivations.php";
    //include "submitactivations.php";
    exit();
}

$deleteSql = "DELETE FROM revisedactivations WHERE season=$season AND week=$week AND teamid=$teamnum";

$posArray = array('HC', 'QB', 'RB', 'WR', 'TE', 'K', 'OL', 'DL', 'LB', 'DB');
$first = true;
$insertSql = "INSERT INTO revisedactivations (season, week, teamid, pos, playerid) VALUES ";
foreach ($_REQUEST as $key => $value) {
    if (array_search($key, $posArray) !== false) {
        foreach ($value as $item) {
            if (!$first) {
                $insertSql .= ", ";
            }
            $first = false;
            $insertSql .= "($season, $week, $teamnum, '$key', $item)";    
        }
    }
}
if ($actID == "on") {
    $insertSql .= ", ($season, $week, $teamnum, 'HC', $actHC)";
}

mysql_query($deleteSql) or die("Unable to clear old activations: ".mysql_error());
mysql_query($insertSql) or die("Unable to add new activations: ".mysql_error());

header("Location: activations.php");
?>
