<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (isset($season)) {
    $lookseason = $season;
} else {
    $lookseason = $currentSeason;
}

$query = "SELECT t.name, CONCAT(p.firstname, ' ',p.lastname), ";
$query .= "p.pos, p.team, pro.cost ";
$query .= "FROM newplayers p, protections pro, team t ";
$query .= "WHERE p.playerid=pro.playerid ";
$query .= "AND pro.season=$lookseason and t.teamid=pro.teamid ";

if (!isset($order) || $order=='team') {
    $query .= "ORDER BY t.name, p.pos, p.lastname ";
    $teamcheck = true;
} else {
    $query .= "ORDER BY p.pos, p.lastname ";
    $teamcheck = false;
}


$title = "WMFFL Protections";
?>

<?
        include "$DOCUMENT_ROOT/base/menu.php"; 
?>      

<H1 ALIGN=Center>Protections</H1>
<HR size = "1">

<P ALIGN=Center><A HREF="showprotections.php?order=team">By Team</A>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<A HREF="showprotections.php?order=pos">By Position</A>
</P>

<TABLE WIDTH=100%>
<?
$result = mysql_query($query) or die("Error: ".mysql_error());
while (list($team, $name, $pos, $nfl, $cost) = mysql_fetch_row($result)) {
    if ($teamcheck) {
        if ($oldteam != $team) {
            print "<TR><TH COLSPAN=4>$team</TH></TR>";
            $oldteam = $team;
            print "<TR><TH ALIGN=Left>Name</TH><TH ALIGN=Left>Position</TH>";
            print "<TH ALIGN=Left>NFL Team</TH><TH ALIGN=Left>Cost</TH></TR>";
        }
        print "<TR><TD>$name</TD><TD>$pos</TD><TD>$nfl</TD>";
        print "<TD>$cost</TD></TR>";
    } else {
        if ($oldpos != $pos) {
            print "<TR><TH COLSPAN=4>$pos</TH></TR>";
            $oldpos = $pos;
            print "<TR><TH ALIGN=Left>Team</TH><TH ALIGN=Left>Name</TH>";
            print "<TH ALIGN=Left>NFL Team</TH><TH ALIGN=Left>Cost</TH></TR>";
        }
        print "<TR><TD>$team</TD><TD>$name</TD><TD>$nfl</TD>";
        print "<TD>$cost</TD></TR>";
    }
}

?>
</TABLE>

<?
        include "$DOCUMENT_ROOT/base/footer.html";
?>


