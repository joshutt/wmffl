<?php
require_once "utils/start.php";

if (!$isin) {
    include "submitactivations.php";
    exit();
}

$actID = array_key_exists("actHC", $_REQUEST) ? $_REQUEST["actHC"] : array();
$actHC = array_key_exists("actHCid", $_REQUEST) ? $_REQUEST["actHCid"] : array();
$HC = array_key_exists("HC", $_REQUEST) ? $_REQUEST["HC"] : array();
$QB = array_key_exists("QB", $_REQUEST) ? $_REQUEST["QB"] : array();
$RB = array_key_exists("RB", $_REQUEST) ? $_REQUEST["RB"] : array();
$WR = array_key_exists("WR", $_REQUEST) ? $_REQUEST["WR"] : array();
$TE = array_key_exists("TE", $_REQUEST) ? $_REQUEST["TE"] : array();
$K = array_key_exists("K", $_REQUEST) ? $_REQUEST["K"] : array();
$OL = array_key_exists("OL", $_REQUEST) ? $_REQUEST["OL"] : array();
$DL = array_key_exists("DL", $_REQUEST) ? $_REQUEST["DL"] : array();
$LB = array_key_exists("LB", $_REQUEST) ? $_REQUEST["LB"] : array();
$DB = array_key_exists("DB", $_REQUEST) ? $_REQUEST["DB"] : array();
$myGP = array_key_exists("myGP", $_REQUEST) ? $_REQUEST["myGP"] : array();
$oppGP = array_key_exists("oppGP", $_REQUEST) ? $_REQUEST["oppGP"] : array();


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
// $deleteGPs = "DELETE FROM gameplan WHERE season=$season AND week=$week AND teamid=$teamnum";

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

/*
$gameplanSql = "INSERT INTO gameplan (season, week, teamid, side, playerid) VALUES ";
$useGp = false;
if (isset($myGP) && $myGP != -1) {
    $useGp = true;
    $gameplanSql .= "($season, $week, $teamnum, 'Me', $myGP)";
}

if (isset($oppGP) && $oppGP != -1) {
    if ($useGp) {
        $gameplanSql .= ", ";
    }
    $useGp = true;
    $gameplanSql .= "($season, $week, $teamnum, 'Them', $oppGP)";
}
*/

mysqli_query($conn, $deleteSql) or die("Unable to clear old activations: " . mysqli_error($conn));
//mysqli_query($conn, $deleteGPs) or die("Unable to clear old gameplan: " . mysqli_error($conn));
mysqli_query($conn, $insertSql) or die("Unable to add new activations: " . mysqli_error($conn));
/*
if ($useGp) {
    mysqli_query($conn, $gameplanSql) or die("Unable to add new gameplan: " . mysqli_error($conn));
}
*/

header("Location: activations.php");
