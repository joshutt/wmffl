Some text here.  This is the PHP TEst
<BR>

You Rolled a 
<? 
	mt_srand((double)microtime()*1000000);	
	print(mt_rand(1,6)); 
	print("<BR>Your Value is: ***".$passed."***<BR>");
?>

<BR>
<TABLE>
<?
	$conn = mysql_connect('localhost','wmffl_other','other');
	$result = mysql_db_query("wmffl_misc","SELECT TeamID, TeamName, Owner FROM teams", $conn);
	while (list($id, $name, $owner) = mysql_fetch_row($result)) {
		print(" <TR>\n".
			  "  <TD>$id</TD>\n".
			  "  <TD>$name</TD>\n".
			  "  <TD>$owner</TD>\n".
			  "  <TD>".mt_rand(1,100)."</TD>\n".
			  " </TR>\n");
	}

?>
</TABLE>
