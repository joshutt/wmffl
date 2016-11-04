<?
require_once "base/conn.php";
require_once "base/useful.php";

if ($viewteam == null) {
    $viewteam = 2;
}
$viewteam = mysql_real_escape_string($viewteam);

$teaminfoSQL = "SELECT t.name as 'teamname', t.member, u.name,
t.logo, t.fulllogo, t.motto, t.teamid, min(o.season) as 'season'
FROM team t LEFT JOIN user u
ON t.teamid=u.teamid
AND u.active='Y'
LEFT JOIN owners o on t.teamid=o.teamid and u.userid=o.userid
WHERE REPLACE(LOWER('$viewteam'), ' ', '') IN (LOWER(t.teamid), LOWER(t.abbrev), replace(LOWER(t.name),' ',''))
ORDER BY u.primaryowner DESC, u.name
";

//print $teaminfoSQL;
//exit(1);

$results = mysql_query($teaminfoSQL) or die("Error in query: ".mysql_error());
$ownerList = null;
$ownCount = 1;
while ($teaminfo = mysql_fetch_array($results)) {
    $teamname = $teaminfo['teamname'];
    if ($ownerList != null) {
        $ownerList .= " and ";
        $ownCount++;
    }
    $viewteam = $teaminfo['teamid'];
    $ownerList .= $teaminfo['name'];
    $teamsince = $teaminfo['member'];
    $ownerSince = $teaminfo['season'];
    $teammotto = "";
    if ($teaminfo['motto'] != null) {
        $teammotto = "\"${teaminfo['motto']}\"";
    }
//    $fulllogo = $teaminfo['fulllogo'];
    $logo = $teaminfo['logo'];
}
$title = "$teamname $title";
if ($ownCount > 1) {
    $ownername = "Owners: $ownerList";
} else {
    $ownername = "Owner: $ownerList";
}

$titleSQL = "SELECT season FROM titles WHERE teamid=$viewteam AND type='League'";
$results = mysql_query($titleSQL) or die("Error: ".mysql_error());
$champyear = array();
while (list($newSeason) = mysql_fetch_array($results)) {
    array_push($champyear, $newSeason);
}

$cssList = array("base/css/team.css");
include "base/menu.php";
?>

<?
if ($fulllogo == 1) {
?>

<center><img src="/teams/<? print $logo; ?>" align="center" alt="<? print $teamname;?>" />
<H5 ALIGN=Center><? print $ownername; ?><BR>Member Since <? print $teamsince; ?><BR>
    <I><? print $teammotto; ?></I></H5>
</center>

<?php } else { ?>

<div id="wrapper">
<div id="teamLogoBlock">
<?php if ($logo != null) { ?>
<div id="logo-left"><img src="/teams/<? print $logo; ?>" alt="<? print $teamname;?>" /></div>
<? } ?>
<div id="team-name">
    <span id="big-name"><? print $teamname; ?></span>
    <span id="est">Established <?= $teamsince; ?></span>
    <span id="ownName"><? print $ownername; ?><BR>Since <? print $ownerSince; ?></span>
</div>
<? if ($logo != null) { ?>
<div id="logo-right"><img src="/teams/<? print $logo; ?>" alt="<? print $teamname;?>" /></div>
<?php } ?>
</div></div>
<?php } ?>

<hr class="bar" />

<?
//$champyear = array (2004);
include "newlinkbar.php";
?>

