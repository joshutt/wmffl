<<<<<<< HEAD
<?
require_once "utils/start.php";

if (isset($_REQUEST["season"])) {
    $season = $_REQUEST["season"];
} else {
    $season = $thisSeason;
}

$sql = "SELECT d.round, d.pick, (t.name) as 'name', ";
$sql .= "if(d.teamid<>d.orgTeam, '*', '') as 'flag' ";
$sql .= "FROM draftpicks d, team t ";
$sql .= "WHERE d.season=$season and d.teamid=t.teamid ";
$sql .= "ORDER BY Round, Pick";

$title = "$season WMFFL Draft Order";
?>
<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<H1 Align=Center>Draft Order</H1>
<HR size = "1">
<P>
This is the official draft order for the <?=$season?> Draft.  The order was determined based on reverse order of finish last season.
</P>

<P>
<TABLE WIDTH=100%>

<?
$results = mysql_query($sql) or die("Database error: ".mysql_error());

$round = 0;
while ($pick = mysql_fetch_array($results)) {
    if ($round <> $pick["round"]) {
        if ($round > 0) {
            print "</ol></td>";
            if ($round % 3 == 0) {
                print "</tr>";
            }
        }
        if ($round % 3 == 0) {
            print "<tr valign=\"top\">";
        }
        $round = $pick["round"];
        print "<td width=\"26\"><b>Round $round</b>";
        print "<ol>";
    }
    print "<li>${pick["name"]} <a href=\"#Notes\">${pick["flag"]}</a></li>";
}
?>

<TR><TD>&nbsp;</TD></TR>

<TR><TD COLSPAN=3><A NAME="Notes"><SUB>* - Pick obtained due to trade
</SUB>
</TD></TR>

</TABLE></P>


=======
<?
require_once "utils/start.php";

if (isset($_REQUEST["season"])) {
    $season = $_REQUEST["season"];
} else {
    $season = $thisSeason;
}

$sql = "SELECT d.round, d.pick, (t.name) as 'name', ";
$sql .= "if(d.teamid<>d.orgTeam, '*', '') as 'flag' ";
$sql .= "FROM draftpicks d, team t ";
$sql .= "WHERE d.season=$season and d.teamid=t.teamid ";
$sql .= "ORDER BY Round, Pick";

$title = "$season WMFFL Draft Order";
?>
<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<H1 Align=Center>Draft Order</H1>
<HR size = "1">
<P>
This is the official draft order for the <?=$season?> Draft.  The order was determined based on reverse order of finish last season.
</P>

<P>
<TABLE WIDTH=100%>

<?
$results = mysql_query($sql) or die("Database error: ".mysql_error());

$round = 0;
while ($pick = mysql_fetch_array($results)) {
    if ($round <> $pick["round"]) {
        if ($round > 0) {
            print "</ol></td>";
            if ($round % 3 == 0) {
                print "</tr>";
            }
        }
        if ($round % 3 == 0) {
            print "<tr valign=\"top\">";
        }
        $round = $pick["round"];
        print "<td width=\"26\"><b>Round $round</b>";
        print "<ol>";
    }
    print "<li>${pick["name"]} <a href=\"#Notes\">${pick["flag"]}</a></li>";
}
?>

<TR><TD>&nbsp;</TD></TR>

<TR><TD COLSPAN=3><A NAME="Notes"><SUB>* - Pick obtained due to trade
</SUB>
</TD></TR>

</TABLE></P>


>>>>>>> 5e27482dbbbc4c7969cfce51101d875bf010a0c6
