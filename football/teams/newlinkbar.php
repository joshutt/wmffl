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

<ul class="teamnav sideButton nav nav-tabs justify-content-center">
    <li class="nav-item nav-link col-4"><a href="teamhistory.php?viewteam=<? print $viewteam; ?>"><img
                    src="/images/football.jpg" border="0"/>History</a></li>
    <li class="nav-item nav-link col-4 active"><a href="teamroster.php?viewteam=<? print $viewteam; ?>"><img
                    src="/images/football.jpg" border="0"/>Roster</a></li>
    <li class="nav-item nav-link col-4"><a href="teamschedule.php?viewteam=<? print $viewteam; ?>"><img
                    src="/images/football.jpg" border="0"/>Schedule</a></li>
</ul>



