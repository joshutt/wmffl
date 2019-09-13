<?
require_once "utils/start.php";

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

$indRec['QB'][1] = 41;
$indRec['QB'][2] = 40;
$indRec['QB'][3] = 40;
$indRec['QB'][4] = 39;
$indRec['QB'][5] = 39;
$indRec['QB'][6] = 38;
$indRec['QB'][7] = 38;
$indRec['QB'][8] = 36;
$indRec['QB'][9] = 36;
$indRec['QB'][10] = 36;
$indRec['QB'][11] = 36;
$indRec['RB'][1] = 49;
$indRec['RB'][2] = 46;
$indRec['RB'][3] = 45;
$indRec['RB'][4] = 44;
$indRec['RB'][5] = 42;
$indRec['RB'][6] = 41;
$indRec['RB'][7] = 35;
$indRec['RB'][8] = 35;
$indRec['RB'][9] = 35;
$indRec['RB'][10] = 35;
$indRec['RB'][11] = 35;
$indRec['WR'][1] = 52;
$indRec['WR'][2] = 40;
$indRec['WR'][3] = 39;
$indRec['WR'][4] = 39;
$indRec['WR'][5] = 37;
$indRec['WR'][6] = 36;
$indRec['WR'][7] = 36;
$indRec['WR'][8] = 36;
$indRec['WR'][9] = 36;
$indRec['WR'][10] = 35;
$indRec['WR'][11] = 35;
$indRec['WR'][12] = 35;
$indRec['TE'][1] = 36;
$indRec['TE'][2] = 35;
$indRec['TE'][3] = 32;
$indRec['TE'][4] = 29;
$indRec['TE'][5] = 26;
$indRec['TE'][6] = 26;
$indRec['TE'][7] = 25;
$indRec['TE'][8] = 25;
$indRec['TE'][9] = 24;
$indRec['TE'][10] = 24;
$indRec['K'][1] = 23;
$indRec['K'][2] = 23;
$indRec['K'][3] = 23;
$indRec['K'][4] = 21;
$indRec['K'][5] = 21;
$indRec['K'][6] = 20;
$indRec['K'][7] = 20;
$indRec['K'][8] = 19;
$indRec['K'][9] = 19;
$indRec['K'][10] = 19;
$indRec['K'][11] = 19;
$indRec['K'][12] = 19;
$indRec['OL'][1] = 33;
$indRec['OL'][2] = 31;
$indRec['OL'][3] = 29;
$indRec['OL'][4] = 28;
$indRec['OL'][5] = 26;
$indRec['OL'][6] = 25;
$indRec['OL'][7] = 25;
$indRec['OL'][8] = 24;
$indRec['OL'][9] = 23;
$indRec['OL'][10] = 23;
$indRec['DL'][1] = 26;
$indRec['DL'][2] = 25;
$indRec['DL'][3] = 23;
$indRec['DL'][4] = 23;
$indRec['DL'][5] = 23;
$indRec['DL'][6] = 22;
$indRec['DL'][7] = 21;
$indRec['DL'][8] = 20;
$indRec['DL'][9] = 20;
$indRec['DL'][10] = 20;
$indRec['DL'][11] = 20;
$indRec['DL'][12] = 20;
$indRec['DL'][13] = 20;
$indRec['LB'][1] = 44;
$indRec['LB'][2] = 33;
$indRec['LB'][3] = 31;
$indRec['LB'][4] = 30;
$indRec['LB'][5] = 29;
$indRec['LB'][6] = 29;
$indRec['LB'][7] = 28;
$indRec['LB'][8] = 26;
$indRec['LB'][9] = 24;
$indRec['LB'][10] = 24;
$indRec['LB'][11] = 24;
$indRec['LB'][12] = 24;
$indRec['DB'][1] = 29;
$indRec['DB'][2] = 28;
$indRec['DB'][3] = 27;
$indRec['DB'][4] = 27;
$indRec['DB'][5] = 26;
$indRec['DB'][6] = 26;
$indRec['DB'][7] = 26;
$indRec['DB'][8] = 26;
$indRec['DB'][9] = 26;
$indRec['DB'][10] = 26;

$seasRec['HC'][1] = 51;
$seasRec['HC'][2] = 51;
$seasRec['HC'][3] = 51;
$seasRec['HC'][4] = 51;
$seasRec['HC'][5] = 49;
$seasRec['HC'][6] = 49;
$seasRec['HC'][7] = 47;
$seasRec['HC'][8] = 46;
$seasRec['HC'][9] = 46;
$seasRec['HC'][10] = 45;
$seasRec['QB'][1] = 322;
$seasRec['QB'][2] = 289;
$seasRec['QB'][3] = 287;
$seasRec['QB'][4] = 253;
$seasRec['QB'][5] = 228;
$seasRec['QB'][6] = 216;
$seasRec['QB'][7] = 210;
$seasRec['QB'][8] = 205;
$seasRec['QB'][9] = 203;
$seasRec['QB'][10] = 196;
$seasRec['RB'][1] = 302;
$seasRec['RB'][2] = 271;
$seasRec['RB'][3] = 262;
$seasRec['RB'][4] = 241;
$seasRec['RB'][5] = 227;
$seasRec['RB'][6] = 216;
$seasRec['RB'][7] = 209;
$seasRec['RB'][8] = 203;
$seasRec['RB'][9] = 197;
$seasRec['RB'][10] = 194;
$seasRec['WR'][1] = 193;
$seasRec['WR'][2] = 192;
$seasRec['WR'][3] = 192;
$seasRec['WR'][4] = 181;
$seasRec['WR'][5] = 175;
$seasRec['WR'][6] = 175;
$seasRec['WR'][7] = 175;
$seasRec['WR'][8] = 174;
$seasRec['WR'][9] = 173;
$seasRec['WR'][10] = 173;
$seasRec['TE'][1] = 133;
$seasRec['TE'][2] = 133;
$seasRec['TE'][3] = 112;
$seasRec['TE'][4] = 101;
$seasRec['TE'][5] = 96;
$seasRec['TE'][6] = 94;
$seasRec['TE'][7] = 93;
$seasRec['TE'][8] = 88;
$seasRec['TE'][9] = 84;
$seasRec['TE'][10] = 72;
$seasRec['K'][1] = 158;
$seasRec['K'][2] = 138;
$seasRec['K'][3] = 138;
$seasRec['K'][4] = 134;
$seasRec['K'][5] = 132;
$seasRec['K'][6] = 132;
$seasRec['K'][7] = 129;
$seasRec['K'][8] = 128;
$seasRec['K'][9] = 120;
#$seasRec['K'][10] = 120;
$seasRec['OL'][1] = 151;
$seasRec['OL'][2] = 147;
$seasRec['OL'][3] = 146;
$seasRec['OL'][4] = 141;
$seasRec['OL'][5] = 138;
$seasRec['OL'][6] = 129;
$seasRec['OL'][7] = 124;
$seasRec['OL'][8] = 122;
$seasRec['OL'][9] = 118;
$seasRec['OL'][10] = 114;
$seasRec['DL'][1] = 125;
$seasRec['DL'][2] = 107;
$seasRec['DL'][3] = 103;
$seasRec['DL'][4] = 99;
$seasRec['DL'][5] = 97;
$seasRec['DL'][6] = 92;
$seasRec['DL'][7] = 91;
$seasRec['DL'][8] = 90;
$seasRec['DL'][9] = 87;
$seasRec['DL'][10] = 86;
$seasRec['LB'][1] = 164;
$seasRec['LB'][2] = 159;
$seasRec['LB'][3] = 159;
$seasRec['LB'][4] = 153;
$seasRec['LB'][5] = 148;
$seasRec['LB'][6] = 135;
$seasRec['LB'][7] = 131;
$seasRec['LB'][8] = 124;
$seasRec['LB'][9] = 124;
$seasRec['LB'][10] = 123;
$seasRec['DB'][1] = 149;
$seasRec['DB'][2] = 146;
$seasRec['DB'][3] = 137;
$seasRec['DB'][4] = 123;
$seasRec['DB'][5] = 122;
$seasRec['DB'][6] = 114;
$seasRec['DB'][7] = 113;
$seasRec['DB'][8] = 111;
$seasRec['DB'][9] = 110;
$seasRec['DB'][10] = 109;

$newRecs = array();

foreach ($indRec as $pos=>$list) {

$tMin = min($list);

$query = <<<EOD
SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name',
wm.weekname as 'week',
ps.active as 'pts'
FROM players p, playerscores ps, weekmap wm
WHERE p.playerid=ps.playerid
AND wm.season=ps.season AND wm.week=ps.week
AND wm.season=2005
AND p.position='$pos'
AND ps.active >= $tMin
ORDER BY ps.active DESC, ps.week
EOD;

    $results = mysqli_query($conn, $query) or die("Error: " . mysqli_error($conn));

$count = 1;
$adj = 0;
$softAdj = 0;
$lastChange = 999;
    while ($rank = mysqli_fetch_array($results)) {
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
FROM players p, playerscores ps
WHERE p.playerid=ps.playerid
AND ps.season=2005
AND p.position='$pos'
GROUP BY p.playerid
HAVING `pts` >= $sMin
ORDER BY `pts` DESC
EOD;

    $results = mysqli_query($conn, $totalQuery) or die("Error: " . mysqli_error($conn));

$count = 1;
$adj = 0;
$softAdj = 0;
$lastChange = 999;
    while ($rank = mysqli_fetch_array($results)) {
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
$dateQuery = "SELECT max(week) FROM playerscores where season=2005 and week<=16";
$dateRes = mysqli_query($conn, $dateQuery);
list($week) = mysqli_fetch_row($dateRes);

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
