<?
require_once "utils/start.php";

#if ($currentWeek == 0) {
#    $week = 1;
#} else {
$week = $currentWeek;
#$week = 13;
#$currentSeason = 2018;
#}

$title = "Waiver Order";
?>

<?
include "base/menu.php";
?>

<h1 align="center">Waiver Wire</h1>
<hr/>

<?php include "transmenu.php"; ?>

<div class="container">
<table>
    <tr>
        <td width="45%" valign="top">
            The waiver selection order for <? print $weekName; ?>.

            <?
            $sql = "SELECT t.name as 'name' FROM team t, waiverorder w WHERE t.teamid=w.teamid AND w.season=$currentSeason AND w.week=$week ORDER BY w.ordernumber";
            $results = mysqli_query($conn, $sql);
            #print $sql;
            print "<ol>";
            while (list($teamSet) = mysqli_fetch_row($results)) {
                print "<li>" . $teamSet . "</li>";
            }
            print "</ol>";
            ?>
        </td>
        <td width="10%"></td>
        <td width="45%" valign="top">
            <? if ($isin) { ?>

                Your current waiver priority for this week:

                <?
                $sql = "select p.firstname, p.lastname, p.pos, p.team from waiverpicks wp join newplayers p on wp.playerid=p.playerid where wp.season=$currentSeason and wp.week=$week and wp.teamid=$teamnum order by wp.priority";
                $results = mysqli_query($conn, $sql);
//print $sql;
                print "<ol>";
                while (list($firstname, $lastName, $pos, $team) = mysqli_fetch_row($results)) {
                    print "<li>$firstname $lastName ($pos-$team)</li>";
                }
                print "</ol>";
            }
            ?>

        </td>
    </tr>
</table>
<hr/>

<p>Last Week's waiver pickups</p>

<? include "listwaiverpicks.php"; ?>
</div>
<? include "base/footer.php"; ?>
