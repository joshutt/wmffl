<?
require_once "utils/start.php";
$query = "SELECT p.firstname, p.lastname, pc.years, CEILING(if(p.pos in ('QB', 'RB', 'WR', 'TE'), pc.years, pc.years/2)) as 'Extra', t.name, p.pos ";
$query .= "FROM newplayers p ";
$query .= "JOIN protectioncost pc ON p.playerid=pc.playerid ";
$query .= "LEFT JOIN roster r ON r.playerid=p.playerid AND r.dateoff is null ";
$query .= "LEFT JOIN team t on r.teamid=t.teamid ";
$query .= "WHERE pc.season=2013 ";
$query .= "GROUP BY p.playerid ";
$query .= "ORDER BY t.name, Extra desc, pc.years desc";

$base = array('HC' => 0, 'QB' => 10, 'RB' => 12, 'WR' => 10, 'TE'=>4, 'K'=>1, 'OL'=>1, 'DL'=>3, 'LB'=>5, 'DB'=>4);

$result = mysqli_query($conn, $query) or die("error: " . mysqli_error($conn));
$count = mysqli_num_rows($result);
$page = array();
while ($aLine = mysqli_fetch_array($result)) {
    $totCost = $base[$aLine['pos']] + $aLine['Extra'];
	$page[$aLine['name']] .= "<TR><TD>".$aLine['firstname']." ".$aLine['lastname'];
    $page[$aLine['name']] .= "</TD><TD ALIGN=Center>".$aLine['pos'];
	$page[$aLine['name']] .= "</TD><TD ALIGN=Center>".$aLine['years']."</TD>";
	$page[$aLine['name']] .= "<TD ALIGN=Center>+".$aLine['Extra']."</TD>";
	$page[$aLine['name']] .= "<TD ALIGN=Center>$totCost</TD></TR>";
    $countall[$aLine['name']]++;
}

$title = "2013 Protection Costs";
?>

<? include "base/menu.php"; ?>

<H1 Align=Center>Protection Costs</H1>
<HR size = "1">
<P>
Under the new protection rules passed in 2011, for the 2012 season and beyond every team will be 
allocated 55 protection points.  Every position receives a base value as follows:

<TABLE BORDER=1>
<TR><TD>QB</TD><TD>RB</TD><TD>WR</TD><TD>TE</TD><TD>K</TD><TD>OL</TD><TD>DL</TD><TD>LB</TD><TD>DB</TD></TR>
<TR><TD>10</TD><TD>12</TD><TD>10</TD><TD>4</TD><TD>1</TD><TD>1</TD><TD>3</TD><TD>5</TD><TD>4</TD></TR>
</TABLE>

<p>Due to 19 WRs being protected in 2012 the cost for WR has been increased to 10 points</p>

<p>Every previous year that a player was protected will increase that player's cost.  For
QB, RB, WR and TE every protected year increases the cost by 1.  For K, OL, DL, LB and DB
the cost increases by 1 for every two years, rounded up.</p>

<p>A team may protect as many players as they want and can afford, however if the total
number of protections at one position in any given season exceeds 16 then the base cost
of that position will perminatenly increase by one the following year.  Points may be
traded.  Any points not used on protections will become transaction points.</p>

<p>Any player not listed on the chart below will have a protection cost equal to
the base of their position.</p>

<TABLE WIDTH=100%>
<TR><TD WIDTH="50%" VALIGN=Top>

<TABLE ALIGN="Center">

<?
$sumup = 0;
foreach ($page as $teamName=>$val) {
    if ($teamName == '') continue;
    if ($sumup > ($count+33)/2) {

?>
</TABLE>
</TD><TD WIDTH=*></TD><TD WIDTH=50% VALIGN=Top>

<TABLE ALIGN="Center" VALIGN=Top>
<?
        $sumup = 0;
    }
?>

<TR><TH COLSPAN=4><? print $teamName; ?></TH></TR>
<TR><TH>Player Name</TH><TH>Pos</TH><TH>Years</TH><TH>Extra</TH><th>Total Cost</th></TR>
<? print $val; ?>
<TR><TD>&nbsp;</TD></TR>

<?
$sumup += $countall[$teamName] + 3;
}
$teamName = '';
?>
<TR><TH COLSPAN=4>Not on a Roster</TH></TR>
<TR><TH>Player Name</TH><TH>Pos</TH><TH>Years</TH><TH>Extra</TH><th>Total Cost</th></TR>
<? print $page['']; ?>
<TR><TD>&nbsp;</TD></TR>

</TABLE>
</TD></TR></TABLE>
<? include "base/footer.php"; ?>
