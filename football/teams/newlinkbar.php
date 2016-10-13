<table width="100%" border="0">

<tr><td align="center">
<?
for ($yearloop=0; $yearloop<count($champyear); $yearloop++) {
	print "<img src=\"/images/greystone15-2a.jpg\" height=100>";
	print "</td><td align=\"center\">";
}
print "</td></tr><tr><td align=\"center\">";
for ($yearloop=0; $yearloop<count($champyear); $yearloop++) {
	print $champyear[$yearloop]."</td><td align=\"center\">";
}
?>
</td></tr>
</table>

<table width="100%">
<tr><td width="5%"></td>
<td><a href="teamhistory.php?viewteam=<? print $viewteam; ?>"><img src="/images/football.jpg" border="0"/>History</a></td>
<td><a href="teamroster.php?viewteam=<? print $viewteam; ?>"><img src="/images/football.jpg" border="0"/>Roster</a></td>
<td><a href="teamschedule.php?viewteam=<? print $viewteam; ?>"><img src="/images/football.jpg" border="0"/>Schedule</a></td>
</tr>
</table>

<hr size="1"/>

