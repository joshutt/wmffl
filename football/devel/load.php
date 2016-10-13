read file
parse line
insert into DB


<?PHP
	$fp = fopen("players.csv","r");
	$linker = mysql_connect('localhost', 'joshutt_footbal', 'wmaccess');
	
	
	while ($data = fgetcsv($fp,1000)) {
		$querystring = "INSERT INTO Players (LastName, FirstName, NFLTeam, Position) VALUES ('".$data[0]."','".$data[1]."','".$data[2]."','".$data[3]."')";
		mysql_db_query("joshutt_oldwmffl", $querystring);
		print(mysql_error()."<BR>".$querystring);
#		print("<BR>".$querystring);
		print("<BR>$data[0]<BR>");
	}
	mysql_close($linker);
	fclose($fp);

?>

<B>DOne</B>
