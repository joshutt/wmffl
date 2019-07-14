<?
require_once "utils/start.php";
if ($currentWeek < 1) {
    $thisSeason = $currentSeason - 1;
} else {
    $thisSeason = $currentSeason;
}

$ptSql = "select t.name, ps.week, ";
$ptSql .= "sum(if(p.pos in ('HC', 'QB', 'RB', 'WR', 'TE', 'K', 'OL'), ps.active, 0)) as 'off', ";
$ptSql .= "sum(if(p.pos in ('DL', 'LB', 'DB'), ps.active, 0)) as 'def' ";
$ptSql .= "from revisedactivations a ";
$ptSql .= "JOIN playerscores ps ON a.season=ps.season and a.week=ps.week and a.playerid=ps.playerid ";
$ptSql .= "JOIN newplayers p ON p.playerid=ps.playerid ";
$ptSql .= "JOIN teamnames t ON a.teamid=t.teamid and a.season=t.season ";
$ptSql .= "where a.season=$thisSeason ";
$ptSql .= "and a.week<=14 ";
$ptSql .= "group by t.teamid, ps.week ";
$ptSql .= "order by ps.week, t.name ";


// Get potential offense and defense scores
$results = mysqli_query($conn, $ptSql);
$potArray = array();
while ($pot = mysqli_fetch_array($results)) {
    $potArray[$pot["week"]][$pot["name"]]["off"] = $pot["off"];
    $potArray[$pot["week"]][$pot["name"]]["def"] = $pot["def"];
}

//print_r($potArray);

$maxWeek = 0;
$reltArray = array();
// Get potential wins and losses
foreach ($potArray as $week=>$scores) {
    if ($week > $maxWeek) {$maxWeek = $week;}
    foreach ($scores as $name=>$posScore) {
        if (!array_key_exists($name, $reltArray)) {
            $reltArray[$name] = array("win" => 0, "lose" => 0, "tie" => 0);
        }
       foreach ($scores as $comName=>$comScore) {
            if ($name == $comName) {continue;}
            $myScore = $posScore["off"] - $comScore["def"];
            $theyScore = $comScore["off"] - $posScore["def"];
            if ($myScore == $theyScore) {
                $reltArray[$name]["tie"]++;
            } else if ($myScore < $theyScore) {
                $reltArray[$name]["lose"]++;
            } else if ($myScore > $theyScore) {
                $reltArray[$name]["win"]++;
            }
       }
    }
}


//print_r($reltArray);
$week = $maxWeek;

$sql = "select tn1.name, s.week, ";
$sql .= "if (t1.teamid=s.teama, s.scorea, s.scoreb) as 'ptsfor', ";
$sql .= "if (t1.teamid=s.teamb, s.scorea, s.scoreb) as 'ptsag' ";
$sql .= "from team t1, team t2, schedule s, teamnames tn1 ";
$sql .= "where s.season=$thisSeason and s.week<=$week ";
$sql .= "and s.week<=14 ";
$sql .= "and t1.teamid in (s.teama, s.teamb) ";
$sql .= "and t2.teamid in (s.teama, s.teamb) and t1.teamid<>t2.teamid ";
$sql .= "and tn1.teamid=t1.teamid and tn1.season=s.season ";
$sql .= "order by s.week, t1.name";

// Get actual wins and losses
$actual = array();
$final = mysqli_query($conn, $sql);
while ($score = mysqli_fetch_array($final)) {
    if (!array_key_exists($score["name"], $actual)) {
        $actual[$score["name"]] = array("wins" => 0, "lose" => 0, "tie" => 0);
    }
    if ($score['ptsfor'] > $score["ptsag"]) {
        $actual[$score["name"]]["wins"]++;
    } else if ($score['ptsfor'] < $score["ptsag"]) {
        $actual[$score["name"]]["lose"]++;
    } else {
        $actual[$score["name"]]["tie"]++;
    }
}

// Get actual win percentage
foreach ($actual as $name=>$resArr) {
    $sumGames = $resArr["wins"]+$resArr["lose"]+$resArr["tie"];
    $pct = ($resArr["wins"]+$resArr["tie"]/2.0)/$sumGames;
 //   printf("%s = %5.3f<BR>",$name, $pct);
    $luck[$name]["act"] = $pct;
}

// Get potential win percentage and luck score
$luckRe = array();
foreach ($reltArray as $name=>$resArr) {
    $sumGames = $resArr["win"]+$resArr["lose"]+$resArr["tie"];
    $pct = ($resArr["win"]+$resArr["tie"]/2.0)/$sumGames;
  //  printf("%s = %5.3f = %d-%d-%d<BR>",$name, $pct, $resArr["win"], $resArr["lose"], $resArr["tie"]);
    $luck[$name]["pot"] = $pct;
    $luckRe[$name] = $luck[$name]["act"]-$pct;
}
//print "Done";

if ($week > 0) {
    $statSig = 100.0/$week;
} else {
    $statSig = 100.0;
}

$title = "Schedule Luck";
?>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>Schedule Luck</H1>
<H5 ALIGN=Center><I>Through Week <?print $week;?></I></H5>
<HR>
<? include "base/statbar.html"; ?>

<P>Schedule Luck is an evaluation of how a team's record compares to what it
"should be".  It is determined by calculating what a team's record would be
if they played every other team, every week and comparing that to what their
record actually is.  A positive number indicates that the team's schedule 
has been favorable to them and that their record is better than it "should"
be.  A negative number indicates that the schedule has been unfavorable. 
The higher the number (positive or negative) the more lucky (or unlucky) the
schedule has been.  Any team whose luck is within the statistical significance
has a fairly accurate record.  These numbers are updated every Tuesday 
afternoon.</P>

<P>Current statistical significance: +/- <? printf("%5.1f",$statSig);?></P>

<TABLE ALIGN=Center>
<TR><TH ALIGN=Left>Team</TH><TH ALIGN=Left>Luck Rating</TH></TR>
<?
arsort($luckRe);
foreach ($luckRe as $name=>$diff) {
//    printf("%s = %5.1f<BR>", $name, ($diff["act"]-$diff["pot"])*100);
//    printf("%s = %5.1f<BR>", $name, $diff*100);
    $color = "Black";
    if ($diff*100 >= $statSig) {
        $color = "Green";
    } else if ($diff*100 <= -$statSig) {
        $color = "Red";
    }
    printf("<TR><TD><FONT color=\"%s\">%s</FONT></TD><TD><FONT color=\"%s\">%5.1f</FONT></TD></TR>", $color, $name, $color, $diff*100);
}
?>
</TABLE>

<? include "base/footer.html"; ?>
