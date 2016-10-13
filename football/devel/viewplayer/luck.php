<?
require_once "$DOCUMENT_ROOT/base/conn.php";
include "$DOCUMENT_ROOT/base/useful.php";

$sql = "select t1.name, s.week, ";
$sql .= "if (t1.teamid=s.teama, s.scorea, s.scoreb) as 'ptsfor', ";
$sql .= "if (t1.teamid=s.teamb, s.scorea, s.scoreb) as 'ptsag' ";
$sql .= "from team t1, team t2, schedule s ";
$sql .= "where s.season=$currentSeason and s.week<$currentWeek ";
$sql .= "and t1.teamid in (s.teama, s.teamb) ";
$sql .= "and t2.teamid in (s.teama, s.teamb) and t1.teamid<>t2.teamid ";
$sql .= "order by s.week, t1.name";


$ptSql = "select t.name, ps.week, ";
$ptSql .= "sum(if(p.position in ('HC', 'QB', 'RB', 'WR', 'TE', 'K', 'OL'), ps.active, 0)) as 'off', ";
$ptSql .= "sum(if(p.position in ('DL', 'LB', 'DB'), ps.active, 0)) as 'def' ";
$ptSql .= "from activations a, playerscores ps, team t, players p ";
$ptSql .= "where a.season=ps.season and a.week=ps.week ";
$ptSql .= "and ps.playerid in (a.HC, a.QB, a.RB1, a.RB2, a.WR1, a.WR2, a.TE, a.K, a.OL, a.DL1, a.DL2, a.LB1, a.LB2, a.DB1, a.DB2) ";
$ptSql .= "and a.season=2003 and t.teamid=a.teamid ";
$ptSql .= "and p.playerid = ps.playerid ";
$ptSql .= "group by t.teamid, ps.week ";
$ptSql .= "order by ps.week, t.name ";


$results = mysql_query($ptSql);
$potArray = array();
while ($pot = mysql_fetch_array($results)) {
    $potArray[$pot["week"]][$pot["name"]]["off"] = $pot["off"];
    $potArray[$pot["week"]][$pot["name"]]["def"] = $pot["def"];
}

$actual = array();
$final = mysql_query($sql);
while ($score = mysql_fetch_array($final)) {
    if ($score['ptsfor'] > $score["ptsag"]) {
        $actual[$score["name"]]["wins"]++;
    } else if ($score['ptsfor'] < $score["ptsag"]) {
        $actual[$score["name"]]["lose"]++;
    } else {
        $actual[$score["name"]]["tie"]++;
    }
}

foreach ($actual as $name=>$resArr) {
    $sumGames = $resArr["wins"]+$resArr["lose"]+$resArr["tie"];
    $pct = ($resArr["wins"]+$resArr["tie"]/2.0)/$sumGames;
 //   printf("%s = %5.3f<BR>",$name, $pct);
    $luck[$name]["act"] = $pct;
}

foreach ($potArray as $week=>$scores) {
    foreach ($scores as $name=>$posScore) {
       foreach ($scores as $comName=>$comScore) {
            if ($name == $comName) {continue;}
            $myScore = $posScore["off"] - $comScore["def"];
            $theyScore = $comScore["off"] - $posScore["def"];
            if ($myScore == $theyScore || ($myScore <= 0 && $theyScore <= 0)) {
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

foreach ($reltArray as $name=>$resArr) {
    $sumGames = $resArr["win"]+$resArr["lose"]+$resArr["tie"];
    $pct = ($resArr["win"]+$resArr["tie"]/2.0)/$sumGames;
  //  printf("%s = %5.3f = %d-%d-%d<BR>",$name, $pct, $resArr["win"], $resArr["lose"], $resArr["tie"]);
    $luck[$name]["pot"] = $pct;
    $luckRe[$name] = $luck[$name]["act"]-$pct;
}
//print "Done";
?>



<HTML>
<HEAD>
<TITLE>Schedule Luck</TITLE>
</HEAD>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<H1 ALIGN=Center>Schedule Luck</H1>
<H5 ALIGN=Center><I>Through Week <?print $week;?></I></H5>
<HR>

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

<P>Current statistical significance: +/- <? printf("%5.1f",100.0/$week);?></P>

<TABLE ALIGN=Center>
<TR><TH ALIGN=Left>Team</TH><TH ALIGN=Left>Luck Rating</TH></TR>
<?
arsort($luckRe);
foreach ($luckRe as $name=>$diff) {
//    printf("%s = %5.1f<BR>", $name, ($diff["act"]-$diff["pot"])*100);
//    printf("%s = %5.1f<BR>", $name, $diff*100);
    printf("<TR><TD>%s</TD><TD>%5.1f</TD></TR>", $name, $diff*100);
}
?>
</TABLE>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
