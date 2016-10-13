<?
require_once "utils/start.php";
if ($currentWeek >= 1) {
    $thisSeason = $currentSeason;
} else {
    $thisSeason = $currentSeason - 1;
}

function powersort($a, $b) {
    $aar = array_reverse($a);
    $bar = array_reverse($b);
    for ($i=0; $i<sizeof($aar); $i++) {
        if ($aar[$i] < $bar[$i]) {
            return 1;
        } elseif ($aar[$i] > $bar[$i]) {
            return -1;
        }
    }
    return 0;
}

$sql = "select w.week, t.name, p.firstname, p.lastname, p.pos, ps.pts, ";
$sql .= "ps.active ";
$sql .= "from roster r, weekmap w, newplayers p, teamnames t, playerscores ps ";
$sql .= "where w.season=$thisSeason and r.dateon <= w.activationdue ";
$sql .= "and (r.dateoff > w.activationdue or r.dateoff is null) ";
$sql .= "and r.playerid=p.playerid and r.teamid=t.teamid ";
$sql .= "and ps.week=w.week and ps.season=w.season and ps.playerid=p.playerid ";
$sql .= "and t.season=ps.season ";
$sql .= "order by w.week, t.name, p.pos, ps.pts desc";

$results = mysql_query($sql);
$potPts = array();
$actPts = array();

$curTeam = "";
$curPos = "";
while ($row = mysql_fetch_array($results)) {
    $week = $row["week"];
    $teamName = $row["name"];
    if ($curTeam != $teamName) {
        $curPos = "";
        $curTeam = $teamName;
        $count = 0;
        if (!array_key_exists($teamName, $potPts)) {
            $potPts[$teamName] = array();
            $actPts[$teamName] = array();
        }
        $ppt = 0;
        $apt = 0;
        $holdplp = 0;
    }

    $plp = $row["pts"];
    $pla = $row["active"];
    $apt += $pla;
    if ($curPos != $row["pos"]) {
        $ppt += $plp;
        $curPos = $row["pos"];
        $count = 0;
    }
  //  if (($curPos == 'RB' || $curPos == 'WR' || $curPos == 'DL' ||
  //      $curPos == 'LB' || $curPos == 'DB') && $count==1) {
  //          $ppt += $plp;
  //  }

    if (($curPos == 'WR' || $curPos == 'DL' || $curPos == 'LB' || $curPos == 'DB') && $count==1) {
        $ppt += $plp;
    }

    if (($curPos == 'RB' && $count == 1) || ($curPos == 'WR' && $count==2) || ($curPos == 'TE' && $count==1)) {
        if ($plp > $holdplp) {
            $ppt -= $holdplp;
            $ppt += $plp;
            $holdplp = $plp;
        }
    }
    $count++;

    $potPts[$teamName][$week] = $ppt;
    $actPts[$teamName][$week] = $apt;
}

$teamArray = array(0);
$powerArray = array();
foreach($potPts as $team=>$teamArray) {
    $weight = 0.0;
    $totPot = 0;
    $totAct = 0;
    $weighPot = 0.0;
    $weighAct = 0.0;
    $powArray = array();
    $flatPower = array();
    foreach($teamArray as $week=>$value) {
#        print "Week: $week - Team: $team - ";
#        print "Pot: $value - Act: ".$actPts[$team][$week]."<BR>";
        $newWeight = sqrt($week);
        $weight += $newWeight;
        $totPot += $value;
        $totAct += $actPts[$team][$week];
        $weighPot += $newWeight * $value;
        $weighAct += $newWeight * $actPts[$team][$week];
        $powArray[$week] = ($weighPot + 2.0*(float)$weighAct) / (3.0*$weight);
        $flatPower[$week] = ($totPot + 2.0*$totAct) / (3.0*$week);
        $power = ($weighPot + 2.0*(float)$weighAct) / (3.0*$weight);
        $flat = ($totPot + 2.0*$totAct) / (3.0*$week);
        $finalPower = ($power+$flat)/2.0;
        $finalPow[$week] = $finalPower;
    }
    $powerArray{$team}=$finalPow;
}

$week = max(array_keys($teamArray));
uasort($powerArray, "powersort");

$lineSQL = "SELECT t1.name, t2.name FROM schedule s, team t1, team t2 ";
$lineSQL .= "WHERE s.teama=t1.teamid AND s.teamb=t2.teamid AND s.season=$thisSeason ";
$lineSQL .= "AND s.week=".($week+1);

$results = mysql_query($lineSQL);
$arra = array();
$arrb = array();
while(list($ta, $tb) = mysql_fetch_row($results)) {
    array_push($arra, $ta);
    array_push($arrb, $tb);
}

$title = "Power Rankings";
?>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>Power Rankings</H1>
<H5 ALIGN=Center><I>Through Week <?print $week;?></I></H5>
<HR>
<? include "base/statbar.html"; ?>

<P>The current power rankings as well as the rankings for the previous two
weeks are listed below.  Power rankings are intended to be an indication of
how good teams are.  They are based on points scored and potential points, with
a weighing factor for more recent games.  Record plays no role in the actual
ranking.  Therefore, it is possible for a team with a poorer record to appear
high in the rankings an vice versa.  Rankings are to be used for entertainment
purposes only.  </P>

<CENTER>
<TABLE>
<TR><TH ALIGN="Left">Team</TH><TH></TH><TH>Current Rating</TH>
<?
if ($week >= 2) {
    print "<th></th><th>Last Week</th>";
}
if ($week >= 3) {
    print "<th></th><th>Week ".($week-2)."</th>";
}
?>
</TR>

<?
foreach($powerArray as $team=>$finalPow) {
    print "<TR><TD>$team</TD><TD>&nbsp;</TD>";
    printf ("<TD ALIGN=\"center\">%6.2f</TD><TD>&nbsp;</TD>", $finalPow[$week]);
    if ($week >= 2) {
        printf ("<TD ALIGN=\"center\">%6.2f</TD><TD>&nbsp;</TD>", $finalPow[$week-1]);
    }
    if ($week >= 3) {
        printf ("<TD ALIGN=\"center\">%6.2f</TD></TR>", $finalPow[$week-2]);
    }
}
?>
</TABLE></CENTER><BR>

<TABLE ALIGN=Center>
<TR><TH COLSPAN=5>Week <?print ($week+1);?> Lines</TH></TR>
<TR><TH>Favorite</TH><TH></TH><TH>Line</TH><TH></TH><TH>Underdog</TH></TR>
<?

for ($i=0; $i<sizeof($arra); $i++) {
$teama = $arra[$i];
$teamb = $arrb[$i];

if ($powerArray{$teama}[$week] > $powerArray{$teamb}[$week]) {
    $line = $powerArray{$teama}[$week] - $powerArray{$teamb}[$week];
    $line *= 2;
    $line = round($line);
    $line /= 2;
    print "<TR><TD>$teama</TD><TD>&nbsp;</TD><TD>$line</TD><TD>&nbsp;</TD><TD>$teamb</TD></TR>";
} else {
    $line = $powerArray{$teamb}[$week] - $powerArray{$teama}[$week];
    $line *= 2;
    $line = round($line);
    $line /= 2;
    print "<TR><TD>$teamb</TD><TD>&nbsp;</TD><TD>$line</TD><TD>&nbsp;</TD><TD>$teama</TD></TR>";
}
}
?>
</TABLE>

<?include "base/footer.html";?>
