<?
$conn = mysql_connect('localhost','joshutt_footbal','wmaccess');
mysql_select_db("joshutt_oldwmffl");

$query = "select * from weekmap where season = 2016;";
$results = mysql_query($query, $conn);
?>

<p>If you see a full table below with the words <b>End of Table</b> at the end, then the database connection is working properly.</p>

<?
print "<table>";
while ($row = mysql_fetch_array($results)) {
	print "<tr>";
	foreach ($row as $item) {
		print "<td>$item</td>";
	}
	print "</tr>";
}
print "</table>";
?>

<b>End of Table</b>
