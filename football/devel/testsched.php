<?
require_once $DOCUMENT_ROOT."/base/conn.php";
$currentSeason = 2003;

$sql = "SELECT s.week, t1.name, s.scorea, t2.name, s.scoreb, w.weekname, ";
$sql .= "MONTHNAME(w.activationdue), DAYOFMONTH(w.activationdue), ";
$sql .= "DAYNAME(w.activationdue), MONTHNAME(w.enddate), DAYOFMONTH(w.enddate) ";
$sql .= "FROM schedule s, team t1, team t2, weekmap w ";
$sql .= "WHERE s.teama = t1.teamid ";
$sql .= "AND s.teamb = t2.teamid ";
$sql .= "AND s.season=$currentSeason ";
$sql .= "AND s.season=w.season AND s.week=w.week ";
$sql .= "ORDER BY s.week, MD5(CONCAT(t1.name, t2.name)) ";

$currentWeekQuery = "SELECT week FROM weekmap WHERE now() between StartDate and EndDate";
$byeWeekQuery = "SELECT t.name FROM nflstatus s, nflteams t WHERE status='B' AND s.nflteam=t.nflteam AND season=$currentSeason and week=";

$weekRe = mysql_query($currentWeekQuery);
list($currentWeek) = mysql_fetch_row($weekRe);
?>

<HTML>
<HEAD>
<TITLE>WMFFL Schedule</TITLE>
</HEAD>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<H1 Align=Center><? print $currentSeason;?> Schedule</H1>
<HR size = "1"><CENTER>

<A HREF="#Week1">Week 1</A> | <A HREF="#Week2">Week 2</A> |
<A HREF="#Week3">Week 3</A> | <A HREF="#Week4">Week 4</A> |
<A HREF="#Week5">Week 5</A> | <A HREF="#Week6">Week 6</A> |
<A HREF="#Week7">Week 7</A> | <A HREF="#Week8">Week 8</A> <BR>
<A HREF="#Week9">Week 9</A> | <A HREF="#Week10">Week 10</A> |
<A HREF="#Week11">Week 11</A> | <A HREF="#Week12">Week 12</A> |
<A HREF="#Week13">Week 13</A> | <A HREF="#Week14">Week 14</A> <BR>
<A HREF="#Playoffs">Playoffs</A> |
<A HREF="#Championship">WMFFL Championship XII</A><HR size = "1"></CENTER>


<?
$results = mysql_query($sql);

$listWeek = 0;
print "<TABLE>";
while ($row = mysql_fetch_array($results)) {
    if ($row[0] != $listWeek) {
        $byes = mysql_query($byeWeekQuery.$row[0]);
        print "</TABLE><P>";
        print "<A NAME=\"".str_replace(" ","",$row[5])."\"><H4>".$row[5]."</H4></A>";
        print "<H5>".$row[6]." ".$row[7]."-";
        if ($row[9] != $row[6]) {
            print $row[9]." ";
        }
        print $row[10];
        if ($row[8] != "Sunday") {
            print " (".$row[8].")";
        }
        $numByes = mysql_num_rows($byes);
        if ($numByes > 0) {
            print "<BR>NFL Byes: ";
            $byeCount = 1;
            while (list($byeTeam) = mysql_fetch_row($byes)) {
                print $byeTeam;
                if ($byeCount+1 == $numByes) {
                    print " and ";
                } else if ($byeCount < $numByes) {
                    print ", ";
                }
                $byeCount++;
            }
        }
        print "</H5>";
        print "<TABLE>";
        $listWeek = $row[0];
    }
    if ($row[4] > $row[2]) {
        $winName = $row[3];
        $winScore = $row[4];
        $loseName = $row[1];
        $loseScore = $row[2];
    } else {
        $winName = $row[1];
        $winScore = $row[2];
        $loseName = $row[3];
        $loseScore = $row[4];
    }
    if ($row[0] < $currentWeek) {
    print "<TR><TD>$winName</TD><TD>$winScore</TD><TD> </TD>";
    print "<TD>$loseName</TD><TD>$loseScore</TD></TR>";
    } else {
    print "<TR><TD>$winName</TD><TD>vs</TD>";
    print "<TD>$loseName</TD></TR>";
    }
}
print "</TABLE>";
?>

<A NAME="Playoffs"<H4><B>Playoffs</B></H4></A>
<H5>December 14-15</H5>
Division Winner #1 vs Wild Card #2<BR>
Division Winner #2 vs Wild Card #1<BR>
Toliet Bowl V: Blue Division #5 vs Orange Division #5
<P>

<A NAME="Championship"<H4><B>WMFFL Championship XII</B></H4></A>
<H5>December 20-22 (Saturday)</H5>
Playoff Winner #1 vs Playoff Winner #2
<P>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
