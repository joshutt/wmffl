<? require $DOCUMENT_ROOT."/login/loginglob.php"; ?>

<?
require_once "$DOCUMENT_ROOT/base/conn.php";

	// DO checking stuff
//	$ErrorMessage = "";
	if (isset($HTTP_POST_VARS["submit"])) {
		$submitted = true;
		if ($RB1 == $RB2) {
			$ErrorMessage .= "You must activate two different Running Backs<BR>";
		}
		if ($WR1 == $WR2) {
			$ErrorMessage .= "You must activate two different Wide Recievers<BR>";
		}
		if ($DL1 == $DL2) {
			$ErrorMessage .= "You must activate two different Defensive Linemen<BR>";
		}
		if ($LB1 == $LB2) {
			$ErrorMessage .= "You must activate two different Linebackers<BR>";
		}
		if ($DB1 == $DB2) {
			$ErrorMessage .= "You must activate two different Defensive Backs<BR>";
		}
		
		// INSERT INTO activations (TeamID, Season, Week, HC, QB, RB1, RB2, WR1, WR2, TE, K, OL, DL1, DL2, LB1, LB2, DB1, DB2)
		// ($teamnum, $Season, $Week, $HC, $QB, $RB1, $RB2, $WR1, $WR2, $TE, $K, $OL, $DL1, $DL2, $LB1, $LB2, $DB1, $DB2)
		
		$thequery = "select weekname from weekmap where week=".$Week." and Season=".$Season." and now()<=ActivationDue";
		$result = mysql_query($thequery, $conn);
		$numrow = mysql_num_rows($result);
		if ($numrow == 0) {
			$ErrorMessage .= "I'm sorry, you missed the activation deadline for Week ".$Week;
		}
		
		$picked["HC"][$HC] = " SELECTED ";
		$picked["QB"][$QB] = " SELECTED ";
		$picked["RB1"][$RB1] = " SELECTED ";
		$picked["RB2"][$RB2] = " SELECTED ";
		$picked["WR1"][$WR1] = " SELECTED ";
		$picked["WR2"][$WR2] = " SELECTED ";
		$picked["TE"][$TE] = " SELECTED ";
		$picked["K"][$K] = " SELECTED ";
		$picked["OL"][$OL] = " SELECTED ";
		$picked["DL1"][$DL1] = " SELECTED ";
		$picked["DL2"][$DL2] = " SELECTED ";
		$picked["LB1"][$LB1] = " SELECTED ";
		$picked["LB2"][$LB2] = " SELECTED ";
		$picked["DB1"][$DB1] = " SELECTED ";
		$picked["DB2"][$DB2] = " SELECTED ";

		if (!isset($ErrorMessage)) {
			$ErrorMessage = "Activations were submitted ";
			
			// Save lineup here
			$thequery = "INSERT INTO activations (TeamID, Season, Week, HC, QB, RB1, RB2, WR1, WR2, TE, K, OL, DL1, DL2, LB1, LB2, DB1, DB2) ";
			$thequery .= "VALUES (".$teamnum.", ".$Season.", ".$Week.", ".$HC.", ".$QB.", ".$RB1.", ".$RB2.", ".$WR1.", ".$WR2.", ".$TE.", ".$K.", ".$OL.", ".$DL1.", ".$DL2.", ".$LB1.", ".$LB2.", ".$DB1.", ".$DB2.")";
			$result = mysql_query($thequery, $conn);
			
			if (!$result) {
				// update
				$thequery = "UPDATE activations SET HC=$HC, QB=$QB, RB1=$RB1, RB2=$RB2, WR1=$WR1, WR2=$WR2, TE=$TE, K=$K, OL=$OL, DL1=$DL1, DL2=$DL2, LB1=$LB1, LB2=$LB2, DB1=$DB1, DB2=$DB2 WHERE Season=$currentseason AND TeamID=$teamnum AND Week=$Week";
				$result = mysql_query($thequery, $conn);
			}
			
			include $DOCUMENT_ROOT . "/activate/submitthanks.php";
			exit;
			
		}
		
	}

?>

<HTML>
<HEAD>
<TITLE>Submit Activations</TITLE>
</HEAD>

<?
	include $DOCUMENT_ROOT . "/base/menu.php";
?>

<H1 ALIGN=Center>Activations</H1>
<HR size = "1">
<TABLE ALIGN=Center WIDTH=100% BORDER=0>
<TD WIDTH=33%><A HREF="activations.php"><IMG SRC="/images/football.jpg" BORDER=0>Current Activations</A></TD>
<TD WIDTH=34%></TD>
<TD WIDTH=33%><A HREF="#Submit"><IMG SRC="/images/football.jpg" BORDER=0>Submit Activations</A></TD>
</TR></TABLE>

<HR size = "1">
<A NAME="Submit">

<?
	if ($isin) {

// Pull names from the database

$starters = array (
		"labels" => array("Head Coach", "Quarterback", "Runningback 1",
						"Runningback 2", "Wide Reciever 1", "Wide Reciever 2",
						"Tight End", "Kicker", "Offensive Line", 
						"Defensive Line 1", "Defensive Line 2", "Linebacker 1",
						"Linebacker 2", "Defensive Back 1", "Defensive Back 2"),
		"positions" => array("HC", "QB", "RB", "RB", "WR", "WR", "TE", "K",
						"OL", "DL", "DL", "LB", "LB", "DB", "DB"),
		"values" => array("HC", "QB", "RB1", "RB2", "WR1", "WR2", "TE", "K",
						"OL", "DL1", "DL2", "LB1", "LB2", "DB1", "DB2")
		);

//	$thequery = "select p.fullname, r.playerid, p.position from roster r, players p where r.teamid=1 and r.dateoff is null and r.playerid=p.playerid order by p.position, p.fullname";
	$thequery = "select p.lastname, r.playerid, p.position, IF(p.firstname <> '', concat(', ',p.firstname), '') from roster r, players p, user t where r.teamid=t.teamid and t.password='".$HTTP_COOKIE_VARS["teamid"]."' and t.username ='".$HTTP_COOKIE_VARS["user"]."' and r.teamid=".$teamnum." and r.dateoff is null and r.playerid=p.playerid order by p.position, p.lastname, p.firstname";
	$result = mysql_query($thequery, $conn);
	while (list($name, $playerid, $position, $first) = mysql_fetch_row($result)) {
		if (isset($counter[$position])) {
			$counter[$position]++;
		} else {
			$counter[$position] = 0;
		}
		// $picked[thepos][theid] = " SELECTED ";
		$players[$position][$counter[$position]] = $name.$first;
		$idnum[$position][$counter[$position]] = $playerid;
	}
	
	$thequery = "select min(week) from weekmap where EndDate>=now()";
	$result = mysql_query($thequery, $conn);
	$curWeek = mysql_fetch_row($result);
	$thequery = "select HC, QB, RB1, RB2, WR1, WR2, TE, K, OL, DL1, DL2, LB1, LB2, DB1, DB2 from activations where season=$currentseason and week<=$curWeek[0] and teamid=$teamnum order by week desc limit 1";
	$result = mysql_query($thequery, $conn);
	if (mysql_num_rows($result) > 0) {
		unset($picked);
		$spot = mysql_fetch_row($result);
		for ($spoter=0; $spoter<15; $spoter++) {
			$picked[$starters["values"][$spoter]][$spot[$spoter]] = " SELECTED ";
		}
	}
?>


<TABLE ALIGN=Center>
<FORM ACTION="submitactivations.php" METHOD="POST">
<TR><TD COLSPAN=2 ALIGN=Center>
	<P><FONT COLOR="Red"><B><? print $ErrorMessage; ?></B></FONT></P>
</TD></TR>
<TR><TD>&nbsp;</TD></TR>

<TR><TD>Week: </TD><TD>
	<INPUT TYPE="hidden" NAME="Season" VALUE="<? print $currentseason; ?>">
	<SELECT NAME="Week">
<?
	$thequery = "select week, IF(now()>=StartDate,'This Week', weekname) from weekmap where EndDate >= now() order by EndDate";
	$result = mysql_query($thequery, $conn);
	while (list ($week, $weekname) = mysql_fetch_row($result)) {
		print "<OPTION VALUE=".$week.">".$weekname."</OPTION>";
	}
?>		
	</SELECT>
</TD></TR>


<?
//	while (list ($key, $value) = each ($starters)) {
	for ($j=0; $j < sizeof($starters["labels"]); $j++) {	
		print "<TR><TD>";
		print $starters["labels"][$j].": </TD><TD>";
		print "<SELECT NAME=\"".$starters["values"][$j]."\">";
		for ($i = 0; $i < sizeof($players[$starters["positions"][$j]]); $i++) {
			print "<OPTION VALUE=\"".$idnum[$starters["positions"][$j]][$i]."\"".$picked[$starters["values"][$j]][$idnum[$starters["positions"][$j]][$i]].">".$players[$starters["positions"][$j]][$i]."</OPTION>";
		}
		print "</SELECT></TD></TR>";
	} 
?>

<TR><TD>&nbsp;</TD></TR>
<TR><TD COLSPAN=2 ALIGN=Center><INPUT TYPE="SUBMIT" NAME="submit" VALUE="Submit Activations"></TD></TR>
</FORM>
</TABLE>
<?
	} else {
?>

<CENTER><B>You must be logged in to submit activations</B></CENTER>

<? } ?>


<?
	include $DOCUMENT_ROOT . "/base/footer.html";
?>
