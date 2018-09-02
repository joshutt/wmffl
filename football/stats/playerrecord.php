<?
require_once "utils/start.php";

if ($currentWeek == 0) {
    $currentSeason = $currentSeason - 1;
}

function getOrd($num) {

    switch($num) {
        case 1 : return "st";
        case 2 : return "nd";
        case 3 : return "rd";
        default : return "th";
    }
}


$indRec = array('QB'=> array(), 'RB'=> array(), 'WR'=> array(), 'TE'=> array(),
            'K'=> array(), 'OL'=> array(), 'DL'=> array(), 'LB'=> array(),
            'DB'=> array());
$seasRec = array('HC'=> array(), 'QB'=> array(), 'RB'=> array(), 'WR'=> array(), 'TE'=> array(),
            'K'=> array(), 'OL'=> array(), 'DL'=> array(), 'LB'=> array(),
            'DB'=> array());

$indRec['QB'][1] = 53;
$indRec['QB'][2] = 45;
$indRec['QB'][3] = 44;
$indRec['QB'][4] = 44;
$indRec['QB'][5] = 43;
$indRec['QB'][6] = 43;
$indRec['QB'][7] = 41;
$indRec['QB'][8] = 41;
$indRec['QB'][9] = 41;
$indRec['QB'][10] = 40;
$indRec['QB'][11] = 40;
$indRec['QB'][12] = 40;
$indRec['QB'][13] = 40;
$indRec['RB'][1] = 49;
$indRec['RB'][2] = 49;
$indRec['RB'][3] = 46;
$indRec['RB'][4] = 45;
$indRec['RB'][5] = 45;
$indRec['RB'][6] = 45;
$indRec['RB'][7] = 44;
$indRec['RB'][8] = 42;
$indRec['RB'][9] = 41;
$indRec['RB'][10] = 41;
$indRec['WR'][1] = 52;
$indRec['WR'][2] = 43;
$indRec['WR'][3] = 41;
$indRec['WR'][4] = 40;
$indRec['WR'][5] = 40;
$indRec['WR'][6] = 39;
$indRec['WR'][7] = 39;
$indRec['WR'][8] = 39;
$indRec['WR'][9] = 39;
$indRec['WR'][10] = 38;
$indRec['WR'][11] = 38;
$indRec['TE'][1] = 36;
$indRec['TE'][2] = 35;
$indRec['TE'][3] = 32;
$indRec['TE'][4] = 31;
$indRec['TE'][5] = 29;
$indRec['TE'][6] = 28;
$indRec['TE'][7] = 26;
$indRec['TE'][8] = 26;
$indRec['TE'][9] = 26;
$indRec['TE'][10] = 25;
$indRec['TE'][11] = 25;
$indRec['TE'][12] = 25;
$indRec['TE'][13] = 25;
$indRec['TE'][14] = 25;
$indRec['TE'][15] = 25;
$indRec['K'][1] = 25;
$indRec['K'][2] = 23;
$indRec['K'][3] = 23;
$indRec['K'][4] = 23;
$indRec['K'][5] = 23;
$indRec['K'][6] = 22;
$indRec['K'][7] = 22;
$indRec['K'][8] = 21;
$indRec['K'][9] = 21;
$indRec['K'][10] = 21;
$indRec['K'][11] = 21;
$indRec['K'][12] = 21;
$indRec['K'][13] = 21;
$indRec['K'][14] = 21;
$indRec['OL'][1] = 34;
$indRec['OL'][2] = 33;
$indRec['OL'][3] = 33;
$indRec['OL'][4] = 31;
$indRec['OL'][5] = 30;
$indRec['OL'][6] = 29;
$indRec['OL'][7] = 29;
$indRec['OL'][8] = 28;
$indRec['OL'][9] = 28;
$indRec['OL'][10] = 28;
$indRec['OL'][11] = 28;
$indRec['DL'][1] = 28;
$indRec['DL'][2] = 27;
$indRec['DL'][3] = 26;
$indRec['DL'][4] = 26;
$indRec['DL'][5] = 25;
$indRec['DL'][6] = 25;
$indRec['DL'][7] = 25;
$indRec['DL'][8] = 24;
$indRec['DL'][9] = 24;
$indRec['DL'][10] = 23;
$indRec['DL'][11] = 23;
$indRec['DL'][12] = 23;
$indRec['DL'][13] = 23;
$indRec['DL'][14] = 23;
$indRec['LB'][1] = 44;
$indRec['LB'][2] = 32;
$indRec['LB'][3] = 31;
$indRec['LB'][4] = 31;
$indRec['LB'][5] = 31;
$indRec['LB'][6] = 30;
$indRec['LB'][7] = 29;
$indRec['LB'][8] = 29;
$indRec['LB'][9] = 29;
$indRec['LB'][10] = 29;
$indRec['DB'][1] = 43;
$indRec['DB'][2] = 40;
$indRec['DB'][3] = 35;
$indRec['DB'][4] = 32;
$indRec['DB'][5] = 30;
$indRec['DB'][6] = 30;
$indRec['DB'][7] = 29;
$indRec['DB'][8] = 29;
$indRec['DB'][9] = 29;
$indRec['DB'][10] = 28;
$indRec['DB'][11] = 28;
$indRec['DB'][12] = 28;

$seasRec['HC'][1] = 89;
$seasRec['HC'][2] = 87;
$seasRec['HC'][3] = 80;
$seasRec['HC'][4] = 80;
$seasRec['HC'][5] = 75;
$seasRec['HC'][6] = 74;
$seasRec['HC'][7] = 74;
$seasRec['HC'][8] = 71;
$seasRec['HC'][9] = 69;
$seasRec['HC'][10] = 68;
$seasRec['HC'][11] = 68;
$seasRec['HC'][12] = 68;
$seasRec['QB'][1] = 348;
$seasRec['QB'][2] = 345;
$seasRec['QB'][3] = 325;
$seasRec['QB'][4] = 322;
$seasRec['QB'][5] = 291;
$seasRec['QB'][6] = 289;
$seasRec['QB'][7] = 287;
$seasRec['QB'][8] = 280;
$seasRec['QB'][9] = 279;
$seasRec['QB'][10] = 271;
$seasRec['QB'][11] = 271;
$seasRec['RB'][1] = 333;
$seasRec['RB'][2] = 302;
$seasRec['RB'][3] = 271;
$seasRec['RB'][4] = 262;
$seasRec['RB'][5] = 241;
$seasRec['RB'][6] = 231;
$seasRec['RB'][7] = 227;
$seasRec['RB'][8] = 224;
$seasRec['RB'][9] = 216;
$seasRec['RB'][10] = 215;
$seasRec['WR'][1] = 202;
$seasRec['WR'][2] = 193;
$seasRec['WR'][3] = 192;
$seasRec['WR'][4] = 192;
$seasRec['WR'][5] = 181;
$seasRec['WR'][6] = 177;
$seasRec['WR'][7] = 175;
$seasRec['WR'][8] = 175;
$seasRec['WR'][9] = 175;
$seasRec['WR'][10] = 174;
$seasRec['WR'][11] = 174;
$seasRec['TE'][1] = 168;
$seasRec['TE'][2] = 138;
$seasRec['TE'][3] = 133;
$seasRec['TE'][4] = 133;
$seasRec['TE'][5] = 131;
$seasRec['TE'][6] = 126;
$seasRec['TE'][7] = 122;
$seasRec['TE'][8] = 118;
$seasRec['TE'][9] = 116;
$seasRec['TE'][10] = 113;
$seasRec['K'][1] = 158;
$seasRec['K'][2] = 155;
$seasRec['K'][3] = 151;
$seasRec['K'][4] = 149;
$seasRec['K'][5] = 147;
$seasRec['K'][6] = 146;
$seasRec['K'][7] = 138;
$seasRec['K'][8] = 138;
$seasRec['K'][9] = 138;
$seasRec['K'][10] = 137;
$seasRec['K'][11] = 137;
$seasRec['OL'][1] = 154;
$seasRec['OL'][2] = 151;
$seasRec['OL'][3] = 151;
$seasRec['OL'][4] = 150;
$seasRec['OL'][5] = 147;
$seasRec['OL'][6] = 146;
$seasRec['OL'][7] = 142;
$seasRec['OL'][8] = 141;
$seasRec['OL'][9] = 138;
$seasRec['OL'][10] = 131;
$seasRec['DL'][1] = 167;
$seasRec['DL'][2] = 133;
$seasRec['DL'][3] = 125;
$seasRec['DL'][4] = 117;
$seasRec['DL'][5] = 116;
$seasRec['DL'][6] = 116;
$seasRec['DL'][7] = 110;
$seasRec['DL'][8] = 109;
$seasRec['DL'][9] = 108;
$seasRec['DL'][10] = 107;
$seasRec['DL'][11] = 107;
$seasRec['LB'][1] = 164;
$seasRec['LB'][2] = 159;
$seasRec['LB'][3] = 159;
$seasRec['LB'][4] = 153;
$seasRec['LB'][5] = 148;
$seasRec['LB'][6] = 146;
$seasRec['LB'][7] = 145;
$seasRec['LB'][8] = 143;
$seasRec['LB'][9] = 139;
$seasRec['LB'][10] = 138;
$seasRec['LB'][11] = 138;
$seasRec['DB'][1] = 149;
$seasRec['DB'][2] = 147;
$seasRec['DB'][3] = 146;
$seasRec['DB'][4] = 137;
$seasRec['DB'][5] = 137;
$seasRec['DB'][6] = 132;
$seasRec['DB'][7] = 124;
$seasRec['DB'][8] = 124;
$seasRec['DB'][9] = 123;
$seasRec['DB'][10] = 122;

$newRecs = array();

foreach ($indRec as $pos=>$list) {

$tMin = min($list);

$query = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name',
wm.weekname as 'week',
ps.active as 'pts'
FROM newplayers p, playerscores ps, weekmap wm
WHERE p.playerid=ps.playerid
AND wm.season=ps.season AND wm.week=ps.week
AND wm.season=$currentSeason
AND p.pos='$pos'
AND ps.active >= $tMin
ORDER BY ps.active DESC, ps.week
EOD;

$results = mysql_query($query) or die("Error: ".mysql_error());

$count = 1;
$adj = 0;
$softAdj = 0;
$lastChange = 999;
while($rank = mysql_fetch_array($results)) {
    //print "{$rank["name"]}<br/>";
//    print "Adding: {$rank["name"]} - {$rank["pts"]} - **$pos**<br/>";
    for ($i = $count; $i<=sizeof($list); $i++) {
        if ($rank["pts"] < $lastChange) {
            $adj = $softAdj;
            $lastChange = $rank["pts"];
        }
        if ($i+$adj > 10) break;
        if ($rank["pts"] > $list[$i]) {
            $toAdd = array($pos, $rank["name"], $rank["week"], $rank["pts"], $i+$adj, '');
            $softAdj++;
//            print "Adding: {$rank["name"]} - {$rank["pts"]} - **$pos**<br/>";
            array_push($newRecs, $toAdd);
            $count = $i;
            continue 2;
        } else if ($rank["pts"] == $list[$i]) {
            $toAdd = array($pos, $rank["name"], $rank["week"], $rank["pts"], $i+$adj, '(tie)');
            $softAdj++;
//            print "Adding: {$rank["name"]} - {$rank["pts"]} - **$pos**<br/>";
            array_push($newRecs, $toAdd);
            $count = $i;
            continue 2;
        }
    }
    break;
}


}


// Season Records
$seaRecs = array();
foreach ($seasRec as $pos=>$list) {

$sMin = min($list);

$totalQuery = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name',
sum(ps.active) as 'pts'
FROM newplayers p, playerscores ps
WHERE p.playerid=ps.playerid
AND ps.season=$currentSeason
AND p.pos='$pos'
GROUP BY p.playerid
HAVING `pts` >= $sMin
ORDER BY `pts` DESC
EOD;

$results = mysql_query($totalQuery) or die("Error: ".mysql_error());

$count = 1;
$adj = 0;
$softAdj = 0;
$lastChange = 999;
while($rank = mysql_fetch_array($results)) {
    //print "{$rank["name"]}<br/>";
    //print "Adding: {$rank["name"]} - {$rank["pts"]} - **$pos**<br/>";
    for ($i = $count; $i<=sizeof($list); $i++) {
        if ($rank["pts"] < $lastChange) {
            $adj = $softAdj;
            $lastChange = $rank["pts"];
        }
        if ($i+$adj > 10) break;
        if ($rank["pts"] > $list[$i]) {
            $toAdd = array($pos, $rank["name"], $rank["pts"], $i+$adj, '');
            $softAdj++;
//            print "Adding: {$rank["name"]} - {$rank["pts"]} - **$pos**<br/>";
            array_push($seaRecs, $toAdd);
            $count = $i;
            continue 2;
        } else if ($rank["pts"] == $list[$i]) {
            $toAdd = array($pos, $rank["name"], $rank["pts"], $i+$adj, '(tie)');
            $softAdj++;
//            print "Adding: {$rank["name"]} - {$rank["pts"]} - **$pos**<br/>";
            array_push($seaRecs, $toAdd);
            $count = $i;
            continue 2;
        }
    }
    break;
}


}
$dateQuery = "SELECT max(week) FROM playerscores where season=$currentSeason and week<=16";
$dateRes = mysql_query($dateQuery);
list($week) = mysql_fetch_row($dateRes);

$title = "Player Records";
include "base/menu.php";
?>

<h1 align="center"><? print $title; ?></h1>
<h5 align="center"><i>Through Week <? print $week; ?></i></h5>
<hr/>

<? include "base/statbar.html"; ?>

<p>This is a list of individual player performances this season, that rank among the top 10 ever at a given position.</p>

<?
$seasonFirst = true;
foreach ($seaRecs as $record) {
    if ($record[3] > 1) {
        continue;
    }

    if ($seasonFirst) {
        print <<<EOD
<p>
<center>
<table border="1">
<tr><th colspan="4">Single Season Records</th></tr>
<tr><th>Pos</th><th>Player</th><th>Pts</th></tr>
EOD;
        $seasonFirst = false;
    }

    print <<<EOD
<tr><td>$record[0]</td><td>$record[1]</td>
<td>$record[2] $record[4]</td></tr>
EOD;
}

if (!$seasonFirst) {
?>
</table>
</center>
</p>
<?
}


$first = true;
foreach ($newRecs as $record) {
    if ($record[4] > 1) {
        continue;
    }

    if ($first) {
        print <<<EOD
<p>
<center>
<table border="1">
<tr><th colspan="4">Single Game Records</th></tr>
<tr><th>Pos</th><th>Player</th><th>Week</th><th>Pts</th></tr>
EOD;
        $first = false;
    }

    print <<<EOD
<tr><td>$record[0]</td><td>$record[1]</td><td>$record[2]</td>
<td>$record[3] $record[5]</td></tr>
EOD;
}

if (!$first) {
?>
</table>
</center>
</p>
<?
}
?>

<?
if (sizeof($seaRecs) > 0) {
?>
<p><center>
<table border="1">
<tr><th colspan="5">Single Season Top Ten</th></tr>
<tr><th>Pos</th><th>Player</th><th>Pts</th><th>Rank</th></tr>
<?
foreach ($seaRecs as $record) {
    if ($record[3] == 1) continue;
    $ordinal = getOrd($record[4]);
    print <<<EOD
<tr><td>$record[0]</td><td>$record[1]</td>
<td>$record[2]</td><td>$record[3]$ordinal $record[4]</td></tr>
EOD;
}

?>
</table>
</center></p>
<?}?>



<?
if (sizeof($newRecs) > 0) {
?>
<center>
<table border="1">
<tr><th colspan="5">Single Game Top Ten</th></tr>
<tr><th>Pos</th><th>Player</th><th>Week</th><th>Pts</th><th>Rank</th></tr>
<?
foreach ($newRecs as $record) {
    if ($record[4] == 1) continue;
    $ordinal = getOrd($record[4]);
    print <<<EOD
<tr><td>$record[0]</td><td>$record[1]</td><td>$record[2]</td>
<td>$record[3]</td><td>$record[4]$ordinal $record[5]</td></tr>
EOD;
}

?>
</table>
</center>
<?}?>

<?
include "base/footer.html";
?>
