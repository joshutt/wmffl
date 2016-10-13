<?
	$title = "Header Info";
	$head = "Header Info";
	include('header.php');
?>

<?
$ip = getenv("REMOTE_USER");
print("***".$ip."***");
phpinfo();
?>


<?
	include('footer.php');
?>