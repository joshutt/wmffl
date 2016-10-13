<?
function process($array, $word="pick") {
	if(is_array($array)) {
//		print("<ul>\n");
//		$playerlist = array();
		while(list($key,$val)=each($array))
		{
			if ($val == null || $val == "") continue;
			if (substr($key, 0, strlen($word)) != $word) continue;
			$playerlist[] = $val;
//			print("<li> $val ");
		}
	}
	return $playerlist;
}

function array_keys ($arr, $term="") {
    $t = array();
    while (list($k,$v) = each($arr)) {
        if ($term && $v != $term)
            continue;
            $t[] = $k;
        }
        return $t;
}
       
// establish connection
$conn = mysql_pconnect('localhost','wmffl_other','other');
mysql_select_db("wmffl_test");

$teamnum = 2;

       
if($submit == "Confirm") {
	$playercount = 0;
	$listcount = 0;
	while(list($key, $val) = each($HTTP_POST_VARS)) {
		$com = substr($key, 0, 4);
		if ($com == "keep") {
			if ($val=="n") {
				print "Dont keep ".substr($key,4)."<BR>";
				$droparray[] = substr($key,4);
			} else {
				$playercount++;
			}
		} else if ($com == "pick" && $val=='y') {
			$playercount++;
			$playlist[] = substr($key,4);
//			$pickup[] = substr($key,4);
			print "Pick Up ".substr($key,4)."<BR>";		
		}
	} 	
	if ($playercount > 25) {
		$ErrorMessage =  "That would give you $playercount players on your roster!!  You must drop someone!! <BR>";
	}
	
	if (!isset($ErrorMessage)) {
		$thequery = "INSERT INTO roster (Playerid, Teamid, Dateon) VALUES ";
		$dropquery = "UPDATE roster SET DateOff=now() WHERE DateOff is null AND (";
		$checkquery = "SELECT r.playerid, p.lastname, p.firstname FROM roster r, players p WHERE r.DateOff is null and r.playerid=p.playerid and r.Playerid=";
		$first = TRUE;
		for ($i=0; $i<sizeof($playlist); $i++) {
			$result = mysql_query($checkquery.$playlist[$i], $conn) or die ("Check Query Failed: ".$playlist[$i]);
			if (mysql_num_rows($result) != 0) {
				$ErrorMessage .= $result[2]." ".$result[1]." is already on a roster!!<BR>";
			} else {
				if (!$first) {$thequery .= ", ";}
				$first = FALSE;
				$thequery .= "(".$playlist[$i].", $teamnum, now())";
			}
		}
		print $thequery;
		for ($i=0; $i<sizeof($droparray); $i++) {
			$dropquery .= "playerid=".$droparray[$i]." OR ";
		}
		$dropquery .= "1=2)";
		if (!isset($ErrorMessage)) {
			mysql_query($dropquery, $conn) or die ("Drop Query Failed");
			mysql_query($thequery, $conn) or die ("Insert Query Failed");
		}
	}
	
} else {
	$playlist = process($HTTP_POST_VARS);
}



// Generate query to list players
$thequery = "SELECT playerid, lastname, firstname, nflteam, position FROM players WHERE playerid in (0 ";
for ($i=0; $i<sizeof($playlist); $i++) {
	$thequery .= ", ".$playlist[$i];
}
$thequery .= ")";

// Get info about players to pickup
$result = mysql_query($thequery, $conn) or die ("Query 1 Failed");
$i = 0;
while ($pickups[$i] = mysql_fetch_row($result)) {
	$i++;
}

// Get info about current roster
$thequery = "select p.playerid, p.lastname, p.firstname, p.nflteam, p.position from players p, roster r, team t where p.playerid=r.playerid and r.teamid=t.teamid and r.dateoff is null and t.teamid=$teamnum order by p.position, p.lastname";
$result = mysql_query($thequery, $conn) or die ("Query 2 Failed");
$i = 0;
while ($currentroster[$i] = mysql_fetch_row($result)) {
	$i++;
}


// Get team info
$thequery = "select count(*), t.ptsleft from roster r, players p, transpoints t where r.playerid=p.playerid and r.teamid=t.teamid and r.teamid=$teamnum and r.dateoff is null and p.position<>'HC' group by t.teamid";
$result = mysql_query($thequery, $conn) or die ("Query 3 Failed");
list($numplayers, $ptsleft) = mysql_fetch_row($result);
?>


<HTML>
<HEAD>
<TITLE>Confirm Transaction</TITLE>
</HEAD>

<? include $DOCUMENT_ROOT . "/base/menu.php"; ?>

<H1 ALIGN=Center>Confirm Transaction</H1>
<HR size = "1">

<P><FONT COLOR="Red"><? print $ErrorMessage; ?></FONT></P>

<P>You currently have <? print $numplayers; ?> players on your roster.  
You have <? print $ptsleft; ?> points left.</P>

<P>Confirm that these are the players you would like to pick up</P>

<TABLE>
<FORM METHOD="POST" ACTION="confirm.php">
<TR><TD><B>Add</B></TD><TD><B>Last Name</B></TD><TD><B>First Name</B></TD><TD><B>NFL Team</B></TD><TD><B>Pos</B></TD></TR>
<?
$i = 0;
while (list($id, $last, $first, $team, $pos) = $pickups[$i]) {
	print "<TR><TD>";
	if ($pos != "HC") {
		print "<SELECT NAME=\"pick$id\"><OPTION VALUE=\"y\">Add</OPTION><OPTION VALUE=\"n\">Leave</OPTION></SELECT>";
	} else {
		print "HC";
	}
	print "</TD><TD>$last</TD><TD>$first</TD><TD>$team</TD><TD>$pos</TD></TR>";
	$i++;
}

?>
</TABLE>

<P>&nbsp;</P>

<TABLE>
<TR><TH ALIGN=Center COLSPAN=5>CURRENT ROSTER</TH></TR>
<TR><TD><B>Status</B></TD><TD><B>Last Name</B></TD><TD><B>First Name</B></TD><TD><B>NFL Team</B></TD><TD><B>Pos</B></TD></TR>
<?
$i = 0;
while (list($id, $last, $first, $team, $pos) = $currentroster[$i]) {
	print "<TR><TD>";
	if ($pos != "HC") {
		print "<SELECT NAME=\"keep$id\"><OPTION VALUE=\"y\">Keep</OPTION><OPTION VALUE=\"n\">Drop</OPTION></SELECT>";
	} else {
		print "HC";
	}
	print "<TD>$last</TD><TD>$first</TD><TD>$team</TD><TD>$pos</TD></TR>";
	$i++;
}

?>

<TR><TD COLSPAN=5 ALIGN=Center><INPUT TYPE="Submit" VALUE="Confirm" NAME="submit"></TD></TR>
</FORM>
</TABLE>

<? include $DOCUMENT_ROOT . "/base/footer.html"; ?>
