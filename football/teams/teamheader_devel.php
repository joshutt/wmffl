<?
require_once "$DOCUMENT_ROOT/base/conn.php";
require_once "$DOCUMENT_ROOT/base/useful.php";

if ($viewteam == null) {
    $viewteam = 2;
}
$viewteam = mysql_real_escape_string($viewteam);

$teaminfoSQL = "SELECT t.name as 'teamname', t.member, u.name,
t.logo, t.fulllogo, t.motto, t.teamid
FROM team t LEFT JOIN user u
ON t.teamid=u.teamid
AND u.active='Y'
WHERE LOWER('$viewteam') IN (LOWER(t.teamid), LOWER(t.abbrev), LOWER(t.name))
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
    $teammotto = "";
    if ($teaminfo['motto'] != null) {
        $teammotto = "\"${teaminfo['motto']}\"";
    }
    $fulllogo = $teaminfo['fulllogo'];
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

include "$DOCUMENT_ROOT/base/menu.php";
?>

<?
if ($fulllogo == 1) {
?>

<center><img src="/teams/<? print $logo; ?>" align="center" alt="<? print $teamname;?>" />
<H5 ALIGN=Center><? print $ownername; ?><BR>Member Since <? print $teamsince; ?><BR>
    <I><? print $teammotto; ?></I></H5>
</center>

<?
} else {
?>

<table width="100%">
<tr>
<? if ($logo != null) { ?>
<td><img src="/teams/<? print $logo; ?>" align="left" alt="<? print $teamname;?>" /></td>
<? } ?>
<td><H1 ALIGN=Center><? print $teamname; ?></H1>
<H5 ALIGN=Center><? print $ownername; ?><BR>Member Since <? print $teamsince; ?><BR>
    <I><? print $teammotto; ?></I></H5>
</td>
<? if ($logo != null) { ?>
<td><img src="/teams/<? print $logo; ?>" align="right" alt="<? print $teamname;?>" /></td>
<? } ?>
</tr>
</table>

<?  } ?>

<hr size="1"/>

<?
//$champyear = array (2004);
include "newlinkbar.php";
?>

