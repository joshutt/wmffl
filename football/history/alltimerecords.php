<?
require_once "utils/start.php";

function cmp($a, $b) {
    if ($a["pct"] > $b["pct"]) {
        return 1;
    } else if ($a["pct"] < $b["pct"]) {
        return -1;
    } else if ($a["games"] > $b["games"]) {
        return 1;
    } else if ($a["games"] < $b["games"]) {
        return -1;
    } else if ($a["wins"] > $b["wins"]) {
        return 1;
    } else if ($a["wins"] < $b["wins"]) {
        return -1;
    } else {
        return 0;
    }
}

function displayBlock($array, $wties = true) {
    $count = 0;
    foreach ($array as $team) {
        $disPCT = sprintf("%3.3f", $team["pct"]);
        if ($team["active"] == 0) {
            $dec = "<i>";
            $ced = "</i>";
        } else {
            $dec = "";
            $ced = "";
        }
        if ($count%2 == 0) {
            $bgcolor = "#cccccc";
        } else {
            $bgcolor = "#ffffff";
        }
        $count++;
        if ($wties) {
            print <<<EOD
    <TR bgcolor="$bgcolor"><TD>$dec{$team["name"]}$ced</TD>
    <TD align="center">$dec{$team["games"]}$ced</TD><TD align="center">$dec{$team["wins"]}$ced</TD>
    <TD align="center">$dec{$team["losses"]}$ced</TD><TD align="center">$dec{$team["ties"]}$ced</TD>
    <TD align="center">$dec$disPCT$ced</TD></TR>
EOD;
        } else {
            print <<<EOD
    <TR bgcolor="$bgcolor"><TD>$dec{$team["name"]}$ced</TD>
    <TD align="center">$dec{$team["games"]}$ced</TD><TD align="center">$dec{$team["wins"]}$ced</TD>
    <TD align="center">$dec{$team["losses"]}$ced</TD><TD align="center">$dec$disPCT$ced</TD></TR>
EOD;
        }
    }
}

function getRecList($addWhere, $season) {
    $alltimeQuery =<<<EOD
        select t.name, t.active, count(s.gameid) as 'games',
        sum(if(t.teamid=s.TeamA, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb>s.scorea, 1, 0))) as 'wins',
        sum(if(t.teamid=s.TeamA, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb<s.scorea, 1, 0))) as 'losses',
        sum(if(s.scorea=s.scoreb, 1, 0)) as 'ties'
        from team t, schedule s
        where t.teamid in (s.TeamA, s.TeamB) and s.season < $season

EOD;
    $groupBy = "group by t.teamid";

    $finalQuery = $alltimeQuery." ".$addWhere." ".$groupBy;
    $result = mysql_query($finalQuery) or die("Dead alltime query: ".$finalQuery."<br/>Error: ".mysql_error());

    $recordsArray = array();
    while ($team = mysql_fetch_array($result)) {
        $pct = ($team["wins"] + $team["ties"]/2.0) / $team["games"];
        $team["pct"]=$pct;
        array_push($recordsArray, $team);
    }
    usort($recordsArray, "cmp");
    return array_reverse($recordsArray);
}


$allTimeArray = getRecList("", $currentSeason); 
$regSeasonArray = getRecList("and postseason=0", $currentSeason);
$postSeasonArray = getRecList("and postseason=1", $currentSeason);
$playoffArray = getRecList("and playoffs=1", $currentSeason);
$championshipArray = getRecList("and championship=1", $currentSeason);
$toiletBowlArray = getRecList("and postseason=1 and playoffs=0", $currentSeason);


$title = "WMFFL ALL-Time Records";
?>

<?
include "base/menu.php";
?>

<H1 ALIGN=CENTER>All-Time Win Loss Records</H1>
<H5 ALIGN=CENTER>Through <? print $currentSeason-1; ?> Season</H5>
<HR size = "1">

<P><TABLE WIDTH=100%>
<TR><TD COLSPAN=6><B>Overall Records</B></TD></TR>
<TR><TD><B>Team</B></TD><TD align="center"><B>Games</B></TD><TD align="center"><B>Wins</B></TD>
<TD align="center"><B>Losses</B></TD><td align="center"><b>Ties</b></td>
<TD align="center"><B>PCT</B></TD></TR>
<? displayBlock($allTimeArray); ?>
</TABLE></P>

<P><TABLE WIDTH=100%>
<TR><TD COLSPAN=6><B>Regular Season Records</B></TD></TR>
<TR><TD><B>Team</B></TD><TD align="center"><B>Games</B></TD><TD align="center"><B>Wins</B></TD>
<TD align="center"><B>Losses</B></TD><td align="center"><b>Ties</b></td>
<TD align="center"><B>PCT</B></TD></TR>
<? displayBlock($regSeasonArray); ?>
</TABLE></P>

<P><TABLE WIDTH=100%>
<TR><TD COLSPAN=5><B>Post-Season Records</B></TD></TR>
<TR><TD><B>Team</B></TD><TD align="center"><B>Games</B></TD><TD align="center"><B>Wins</B></TD>
<TD align="center"><B>Losses</B></TD><TD align="center"><B>PCT</B></TD></TR>
<? displayBlock($postSeasonArray, false); ?>
</TABLE></P>

<P><TABLE WIDTH=100%>
<TR><TD COLSPAN=5><B>Playoff Records</B></TD></TR>
<TR><TD><B>Team</B></TD><TD align="center"><B>Games</B></TD><TD align="center"><B>Wins</B></TD>
<TD align="center"><B>Losses</B></TD><TD align="center"><B>PCT</B></TD></TR>
<? displayBlock($playoffArray, false); ?>
</TABLE></P>

<P><TABLE WIDTH=100%>
<TR><TD COLSPAN=5><B>Championship Game Records</B></TD></TR>
<TR><TD><B>Team</B></TD><TD align="center"><B>Games</B></TD><TD align="center"><B>Wins</B></TD>
<TD align="center"><B>Losses</B></TD><TD align="center"><B>PCT</B></TD></TR>
<? displayBlock($championshipArray, false); ?>
</TABLE></P>

<P><TABLE WIDTH=100%>
<TR><TD COLSPAN=5><B>Toilet Bowl Game Records</B></TD></TR>
<TR><TD><B>Team</B></TD><TD align="center"><B>Games</B></TD><TD align="center"><B>Wins</B></TD>
<TD align="center"><B>Losses</B></TD><TD align="center"><B>PCT</B></TD></TR>
<? displayBlock($toiletBowlArray, false); ?>
</TABLE></P>

<? include "base/footer.html" ?>
