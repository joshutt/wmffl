<?
require_once "utils/start.php";

$sql = "SELECT d.round, d.pick, substring(t.name, 1, 20) as 'name', ";
$sql .= "if(d.teamid<>d.orgTeam, '*', '') as 'flag' ";
$sql .= "FROM draftpicks d, team t ";
$sql .= "WHERE d.season=2008 and d.teamid=t.teamid ";
$sql .= "ORDER BY Round, Pick";

$title = "2008 WMFFL Draft Order";
?>
<? include "base/menu.php"; ?>

<H1 Align=Center>Draft Order</H1>
<H5 ALIGN=Center><I>August 4, 2008</I></H5>
<HR size = "1">
<P>
This is the offical draft order for the 2008 Draft to be held at the Ward's home
on August 31 at 12:30.  Also remember that your protections are due by Sunday,
August 24.  The order was determined based on a <a href="/transactions/draftorder/index.php">lottery</a> of non-playoff
teams.
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

<P><TABLE WIDTH=100% BORDER=1 ALIGN=Center>
<TR><TD ALIGN=Center COLSPAN=3><B>Previous 1st Picks</B></TD></TR>
<TR><TD ALIGN=Left>1992</TD><TD ALIGN=Left>Legions of Byron</TD><TD>Barry Sanders</TD></TR>
<TR><TD ALIGN=Left>1993</TD><TD ALIGN=Left>Slayers</TD><TD>Sterling Sharpe</TD></TR>
<TR><TD ALIGN=Left>1994</TD><TD ALIGN=Left>Norsemen</TD><TD>Seattle QB</TD></TR>
<TR><TD ALIGN=Left>1995</TD><TD ALIGN=Left>Warriors</TD><TD>Dallas QB</TD></TR>
<TR><TD ALIGN=Left>1996</TD><TD ALIGN=Left>Renegades</TD><TD>Lawrence Philips</TD></TR>
<TR><TD ALIGN=Left>1997</TD><TD ALIGN=Left>Iradicators</TD><TD>Emmitt Smith</TD></TR>
<TR><TD ALIGN=Left>1998</TD><TD ALIGN=Left>Barbarians</TD><TD>Corey Dillion</TD></TR>
<TR><TD ALIGN=Left>1999</TD><TD ALIGN=Left>Archers Who Say Ni</TD><TD>Terrell Owens</TD></TR>
<TR><TD ALIGN=Left>2000</TD><TD ALIGN=Left>Hempaholics</TD><TD>Albert Connell</TD></TR>
<TR><TD ALIGN=Left>2001</TD><TD ALIGN=Left>ZEN</TD><TD>Michael Bennett</TD></TR>
<TR><TD ALIGN=Left>2002</TD><TD ALIGN=Left>Barbarians</TD><TD>Rich Gannon</TD></TR>
<TR><TD ALIGN=Left>2003</TD><TD ALIGN=Left>Crusaders</TD><TD>Edgerrin James<TD></TR>
<TR><TD ALIGN=Left>2004</TD><TD ALIGN=Left>Whiskey Tango</TD><TD>Derrick Mason<TD></TR>
<TR><TD ALIGN=Left>2005</TD><TD ALIGN=Left>Lindbergh Baby Casserole</TD><TD>Brian Westbrook<TD></TR>
<TR><TD ALIGN=Left>2006</TD><TD ALIGN=Left>Go Balls Deep</TD><TD>Reggie Bush<TD></TR>
<TR><TD ALIGN=Left>2007</TD><TD ALIGN=Left>Gallic Warriors</TD><TD>Travis Henry<TD></TR>
<TR><TD ALIGN=Left>2008</TD><TD ALIGN=Left>Gallic Warriors</TD><TD>?????<TD></TR>
</TABLE>
</P>

<!--#include virtual="/base/footer.html"-->
