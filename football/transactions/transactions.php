<?
require_once "utils/start.php";

function trade($teamid, $date)
{
    global $conn;
    $tradequery = "select t1.tradegroup, t1.date, tm1.name as TeamFrom, ";
    $tradequery .= "p.lastname, p.firstname, p.pos, p.team, t1.other ";
    $tradequery .= "from trade t1 ";
    $tradequery .= "left join trade t2 on t1.tradegroup=t2.tradegroup and t1.teamfromid<>t2.teamfromid ";
    $tradequery .= "join teamnames tm1 on t1.teamfromid=tm1.teamid ";
    $tradequery .= "left join team tm2 on t2.teamfromid=tm2.teamid ";
    $tradequery .= "join weekmap wm on tm1.season=wm.season ";
    $tradequery .= "left join newplayers p on p.playerid=t1.playerid ";
    $tradequery .= "where (t1.TeamFromid=$teamid or t1.TeamToid=$teamid) ";
    $tradequery .= "and t1.date='$date' ";
    $tradequery .= "and '$date' between wm.startDate and wm.enddate ";
    $tradequery .= "group by t1.tradegroup, abs(tm1.teamid-$teamid), p.lastname ";

    $results = mysqli_query($conn, $tradequery);
    $oldgroup = 0;
    //print mysqli_num_rows($results);
    //print $tradequery;
    while (list($group, $date, $TeamFrom, $lastname, $firstname, $position, $nflteam, $other) = mysqli_fetch_row($results)) {
        if ($oldgroup != $group) {
            print "<LI>Traded ";
            $oldgroup = $group;
            $firstteam = $TeamFrom;
            $firstplayer = TRUE;
        }
        if ($firstteam != $TeamFrom) {
            print " to the $TeamFrom in exchange for ";
            $firstplayer = TRUE;
            $firstteam = $TeamFrom;
        }
        if (!$firstplayer) {
            print ", ";
        }
        if ($other) {
            print $other;
        } else print "$firstname $lastname ($position-$nflteam)";
        $firstplayer = FALSE;
    }
}


$thequery = "SELECT DATE_FORMAT(max(date), '%m/%e/%Y'), DATE_FORMAT(max(date),'%m'), DATE_FORMAT(max(date),'%Y') FROM transactions";
$results = mysqli_query($conn, $thequery);
list($lastupdate, $themonth, $theyear) = mysqli_fetch_row($results);

if (isset($_REQUEST["month"])) $themonth = $_REQUEST["month"];
if (isset($_REQUEST["year"])) $theyear = $_REQUEST["year"];
//	if (!isset($_GET["year"])) $_GET["year"]=2002;

$title = "WMFFL Transactions";
include "base/menu.php";
?>

    <H1 ALIGN=Center>Transactions</H1>
    <H5 ALIGN=Center>Last Updated <? print $lastupdate; ?></H5>
    <HR size="1">

<?php include "transactions/transmenu.php"; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col"><a class="btn btn-wmffl" href="transactions?year=<?= $themonth > 8 ? $theyear : $theyear-1 ?>&month=<?= $themonth > 8 ? $themonth-1 : 12 ?>"><< Previous Month</a></div>
            <div class="col"><a class="btn btn-wmffl" href="transactions?year=<?= $themonth < 12 ? $theyear : $theyear+1 ?>&month=<?= $themonth < 12 ? $themonth+1 : 8 ?>">Next Month >></a></div>
        </div>
        <div class="row py-4">
            <div class="col-12">

        <?php
        // Create the query
        $thequery = "SELECT DATE_FORMAT(t.date, '%M %e, %Y'), m.name, t.method, concat(p.firstname, ' ', p.lastname), p.pos, 
p.team, m.teamid, DATE_FORMAT(t.date, '%Y-%m-%d') 
FROM transactions t, teamnames m, newplayers p 
WHERE t.teamid=m.teamid AND t.playerid=p.playerid
AND m.season=$theyear 
";

        if ($themonth > 8) {
            $thequery .= "AND t.date BETWEEN '" . $theyear . "-" . $themonth . "-01' AND ";
            $thequery .= "'" . $theyear . "-" . $themonth . "-31 23:59:59.99999' ";
        } else {
            $thequery .= "AND t.date BETWEEN '" . $theyear . "-01-01' AND ";
            $thequery .= "'" . $theyear . "-08-31 23:59:59.99999' ";
        }
        $thequery .= "ORDER BY DATE_FORMAT(t.date, '%Y/%m/%d') DESC, m.name, t.method, p.lastname";

        $results = mysqli_query($conn, $thequery) or die("Error: " . mysqli_error($conn));
        $first = TRUE;
        $olddate = "";
        $oldteam = "";
        $oldmethod = "";
        while (list($date, $teamname, $method, $player, $position, $nflteam, $teamid, $rawdate) = mysqli_fetch_row($results)) {
            $change = FALSE;
            if ($olddate != $date) {
                if (!$first) {
                    print "</UL></UL>";
                }
                $first = FALSE;
                print "<B><I>$date</I></B><UL>";
                $olddate = $date;
                $change = TRUE;
                $firstplayer = TRUE;
                $tradeonce = FALSE;
            }
            if ($oldteam != $teamname || $change) {
                if (!$change) print "</UL>";
                print "<LI><B>$teamname</B><UL>";
                $oldteam = $teamname;
                $change = TRUE;
                $firstplayer = TRUE;
                $tradeonce = FALSE;
            }
            if ($oldmethod != $method || $change) {
                switch ($method) {
                    case 'Cut':
                        print "<LI>Dropped ";
                        break;
                    case 'Sign':
                        print "<LI>Picked Up ";
                        break;
                    case 'Trade':
                        if ($tradeonce) continue 2;
                        trade($teamid, $rawdate);
                        $change = TRUE;
                        $oldmethod = "";
                        $tradeonce = TRUE;
                        continue 2;
                    case 'Fire':
                        print "<LI>Fired ";
                        break;
                    case 'Hire':
                        print "<LI>Hired ";
                        break;
                    case 'To IR':
                        print "<li>Moved to IR ";
                        break;
                    case 'From IR':
                        print "<li>Activated from IR ";
                        break;
                }
//			print "<LI>$method ";
                $oldmethod = $method;
                $change = TRUE;
                $firstplayer = TRUE;
            }
            if (!$firstplayer) print ", ";
            print "$player ($position-$nflteam)";
            $firstplayer = FALSE;
        }
        ?>
        </UL></UL>
            </div></div>

        <div class="row">
            <div class="col"><a class="btn btn-wmffl" href=""><< Previous Month</a></div>
            <div class="col"><a class="btn btn-wmffl" href="">Next Month >></a></div>
        </div>
    </div>

<?php include "base/footer.html";

