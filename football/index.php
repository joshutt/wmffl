<? 
$title = "The WMFFL Fantasy Football League";

include "base/menu.php";

?>

<style>
<!--
H4 {font-size:10pt; text-decoration:italic}
.SectionHeader {color:e2a500; text-decoration:bold; font-size:14pt}
-->
</style>

<table width="100%" align="center" valign="top" bgcolor="#660000">
<tr><td align="center"><font color="#e2a500"><b>WASHINGTON METROPOLITAN FANTASY FOOTBALL LEAGUE</b></td></tr>
</table>

<table width="100%" border="0">
<TR><TD VALIGN="top" width="100%">

<?
include "article.php";
include "quicklinks.php";
print "<p>";
include "base/statbar.html";
?>


</TD>

<td align="right" valign="top" width="244">
<table id="rightbar" width="244">
<tr><td>
<?
	include "scores.php";
	print "</td></tr>";
	print "<tr><td>";
	include "list.php";
	print "</td></tr>";
	print "<tr><td>";
	include "forum/commentlist.php";
?>
</td></tr>
</table>
</td>

</TR>

<tr><td align="center" colspan="3">
<?
//include "$DOCUMENT_ROOT/base/statbar.html";
?>
</td></tr>

<TD ALIGN=right WIDTH=244 VALIGN=top>
</TD>


</TABLE>

<?
include "base/footer.html";
?>
