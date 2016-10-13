<? 
$fd = fopen("readdata.txt", "r");
$team = fgets($fd, 4096);
$headline = fgets($fd, 4096);
while (!feof($fd)) {
	$buffer = $buffer.fgets($fd, 4096)."<BR>";
}
fclose($fd);
?>


<?

$title = $team.": ".$headline;
$head = $headline;
include('header.php');

echo $buffer;
?>

Stuff

<?
	include('footer.php');
?>