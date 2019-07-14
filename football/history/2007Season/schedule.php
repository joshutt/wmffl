<?
require_once "utils/start.php";
$thisSeason = 2007;
$thisWeek = 17;
//$thisWeek = $currentWeek;

$sql = "SELECT s.week, substring(t1.name, 1, 20), s.scorea, substring(t2.name, 1, 20), s.scoreb, w.weekname, ";
$sql .= "MONTHNAME(w.activationdue), DAYOFMONTH(w.activationdue), ";
$sql .= "DAYNAME(w.activationdue), MONTHNAME(DATE_SUB(w.enddate, INTERVAL 1 DAY)), DAYOFMONTH(DATE_SUB(w.enddate, INTERVAL 1 DAY)) ";
$sql .= ", s.label, s.postseason ";
//$sql .= "FROM schedule s, teamnames t1, teamnames t2, weekmap w ";
$sql .= "FROM schedule s ";
$sql .= "JOIN weekmap w ON s.season=w.season AND s.week=w.week ";
$sql .= "LEFT JOIN teamnames t1 ON s.teama=t1.teamid and s.season=t1.season ";
$sql .= "LEFT JOIN teamnames t2 ON s.teamb=t2.teamid and s.season=t2.season ";
//$sql .= "WHERE s.teama = t1.teamid ";
//$sql .= "AND s.teamb = t2.teamid ";
//$sql .= "AND s.season=t1.season AND s.season=t2.season ";
$sql .= "WHERE s.season=$thisSeason ";
$sql .= "ORDER BY s.week, s.label, MD5(CONCAT(t1.name, t2.name)) ";

$currentWeekQuery = "SELECT week FROM weekmap WHERE curdate() between StartDate and EndDate";
$byeWeekQuery = "SELECT if(t.name='New York', concat(t.name, ' ', t.nickname), t.name) FROM nflstatus s, nflteams t WHERE status='B' AND s.nflteam=t.nflteam AND season=$thisSeason and week=";

$title = "WMFFL Schedule";
?>

<style>
<!--

a.return {
    font-size: 10pt;
}

.bg0 {
    background-color:#BC9F8E;
    color:#FFFFFF;
    font-weight:bold;
}

.bg0font, .bg0font a, a.bg0font {
    color:#FFFFFF;
    font-weight:bold;
}

.bg1 {
    background-color:#B9B9B9;
    font-weight:bold;
}

.bg2 {
    background-color:#EAE8E8;
}

.bg4 {
    background-color:#CCCCCC;
}

.bg4font {
    font-size: 10pt;
}

.score {
    text-align: center;
}

.SLTables1, .SLTables1 td, .SLTables1 th, form {
    font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;
}
-->
</style>

<? include "base/menu.php"; ?>

<H1 Align=Center><? print $thisSeason;?> Schedule</H1>
<HR size = "1"><CENTER>

<A HREF="#Week1">Week 1</A> | <A HREF="#Week2">Week 2</A> |
<A HREF="#Week3">Week 3</A> | <A HREF="#Week4">Week 4</A> |
<A HREF="#Week5">Week 5</A> | <A HREF="#Week6">Week 6</A> |
<A HREF="#Week7">Week 7</A> | <A HREF="#Week8">Week 8</A> <BR>
<A HREF="#Week9">Week 9</A> | <A HREF="#Week10">Week 10</A> |
<A HREF="#Week11">Week 11</A> | <A HREF="#Week12">Week 12</A> |
<A HREF="#Week13">Week 13</A> | <A HREF="#Week14">Week 14</A> <BR>
<A HREF="#Playoffs">Playoffs</A> |
<A HREF="#Championship">WMFFL Championship XVI</A><HR size = "1"></CENTER>


<?
$results = mysqli_query($conn, $sql) or die("Unable to get games: " . mysqli_error($conn));

$listWeek = 0;
$lastLabel = "";
while ($row = mysqli_fetch_array($results)) {
    if ($row[0] != $listWeek || $row[11] != $lastLabel) {
        if ($listWeek != 0) {
            print <<<EOD
</tbody>
</table>
<a href="#" class="return">Return to top</a>
</div>
<br/>
EOD;
        }


        $byes = mysqli_query($conn, $byeWeekQuery . $row[0]);
        $anchorName = str_replace(" ","",$row[5]);
        $displayWeek = $row[5];
        if ($row[11] != "") {
            $displayWeek = $row[11];
        }

        print <<<EOD
        <a name="$anchorName"/>
        <div class="SLTables1">
<table width="500" cellspacing="1" cellpadding="2" border="0" class="SLTables1">
    <tbody>
    <tr class="bg0" align="center">
        <td class="bg0" colspan="6">
            <font class="bg0font">$displayWeek</font>
        </td>
    </tr>
EOD;
        print <<<EOD
    <tr class="bg1" align="center">
        <td class="bg1" colspan="6">
            <font class="bg1font"><b>{$row[8]}, {$row[6]} {$row[7]}</b></font>
        </td>
    </tr>
EOD;
        $numByes = mysqli_num_rows($byes);
        if ($numByes > 0) {
            $byeCount = 1;
            $byeList = "";
            while (list($byeTeam) = mysqli_fetch_row($byes)) {
                $byeList .= $byeTeam;
                if ($byeCount+1 == $numByes) {
                    $byeList .= " and ";
                } else if ($byeCount < $numByes) {
                    $byeList .= ", ";
                }
                $byeCount++;
            }
        print <<<EOD
    <tr id="main" class="bg4" align="left">
        <td class="bg4" colspan="6">
            <font class="bg4font">NFL Byes: $byeList</font>
        </td>
    </tr>
EOD;
        }
        $listWeek = $row[0];
        $lastLabel = $row[11];
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

    print "<tr class=\"bg2\" valign=\"middle\" height=\"17\" align=\"right\">";
    if ($row[0] < $thisWeek) {
        print "<td align=\"left\" width=\"200\">$winName</td>";
        print "<td align=\"center\" width=\"40\">$winScore</td>";
        print "<td align=\"left\" width=\"20\">&nbsp;</td>";
        print "<td align=\"left\" width=\"200\">$loseName</td>";
        print "<td align=\"center\" width=\"40\">$loseScore</td>";
    } else {
        print "<td align=\"left\" width=\"200\">$winName</td><td width=\"40\">&nbsp;</td>";
        print "<td align=\"left\" width=\"20\">vs</td><td width=\"40\">&nbsp;</td>";
        print "<td align=\"left\" width=\"200\">$loseName</td>";
    }
    print "</tr>";
}

print "</tbody></table>";
print "<a href=\"#\" class=\"return\">Return to top</a>";
print "</div><br/>";


?>

<a name="Playoffs"/><a name="Championship"/>

<? include "base/footer.html"; ?>
