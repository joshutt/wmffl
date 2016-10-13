<?
session_start();
// establish connection
require "utils/start.php";
include_once "base/useful.php";


	$onTeamQuery = "SELECT s.name as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p, roster r, team s WHERE p.playerid=r.playerid AND r.teamid=s.teamid AND r.dateoff IS NULL ";
	$neverTeamQuery = "SELECT 'Available' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p LEFT JOIN roster r ON p.playerid = r.playerid WHERE r.dateon IS NULL ";
	$noOnTeamQuery = "SELECT 'Available' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p, roster r WHERE p.playerid = r.playerid ";
	//$neverTeamQuery = "SELECT 'Available       ' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p LEFT JOIN roster r ON p.playerid = r.playerid WHERE r.dateon IS NULL ";
	//$noOnTeamQuery = "SELECT 'Available       ' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid FROM newplayers p, roster r WHERE p.playerid = r.playerid ";
	$noOnGroupQuery = "GROUP BY p.playerid HAVING COUNT(r.dateon) = COUNT(r.dateoff) ";
	
	$onroster = "r.dateoff is null and r.dateon is not null ";
	$offroster = "r.dateoff is not null or t.dateon is null ";
	//$whereclause = "AND p.status<>'R' AND p.status<>'O' ";
	$whereclause = "AND p.active=1 AND p.usePos=1 ";

    if (!isset($Position)) {$Position="";}
    if (!isset($Team)) {$Team="ANY";}
    if (!isset($Last)) {$Last="";}
    if (!isset($First)) {$First="";}
    if (!isset($Available)) {$Available="";}
    if (!isset($Order)) {$Order="";}
    $theset = FALSE;

	if (isset($submit)) {
		$theset = TRUE;
        // Team is first to allow retired
		if ($Team != "") {
			if ($Team == "NONE") {			
				$whereclause .= "AND (p.team='' OR p.team is null) ";
			} else if ($Team == "ANY") {
				$whereclause .= "AND p.team<>'' AND p.team is not null ";
			} else if ($Team == "RET") {
                // don't start with <>'R' for retired
				//$whereclause = "AND p.status='R' ";
				$whereclause = "AND p.retired is not null ";
			} else {
				$whereclause .= "AND p.team='$Team' ";
			}
		}
		if ($Position != "") {
			$whereclause .= "AND p.pos='$Position' ";
		}
		if ($Last != "") {
			$whereclause .= "AND p.lastname like '%$Last%' ";
		}
		if ($First != "") {
			$whereclause .= "AND p.firstname like '%$First%' ";
		}
		
		if ($Available == "available") {
//			$whereclause .= "( $offroster )";
			$search = "( $neverTeamQuery $whereclause ) UNION ( $noOnTeamQuery $whereclause $noOnGroupQuery ) ";
		} else if ($Available == "taken") {
			$search = $onTeamQuery.$whereclause;
//			$whereclause .= $onroster;
		} else {
			//$search = "( $neverTeamQuery $whereclause ) UNION ( $noOnTeamQuery $whereclause $noOnGroupQuery ) UNION ( $onTeamQuery $whereclause ) ";
			$search = "($neverTeamQuery $whereclause ) UNION  ($noOnTeamQuery $whereclause $noOnGroupQuery)  UNION  ($onTeamQuery $whereclause)  ";
//			$whereclause .= "1=1 ";
		}
		
		
		if ($Order == "") $Order="lastname";
		$orderby = "ORDER BY ".$Order;
		
		//$result = mysql_query("$search where $whereclause $orderby", $conn) or die("query");
        //print $search.$orderby;
		$result = mysql_query($search.$orderby) or 
//        print mysql_error();
die("query: $search$orderby<br/>".mysql_error());
        

	} 
?>

<HTML>
<HEAD>
<TITLE>WMFFL Players List</TITLE>
</HEAD>

<?
include  "base/menu.php"; ?>

<H1 ALIGN=Center>List Players</H1>
<HR size = "1">

<? 
if($isin) {
?>

<P>Step 1: Search for players by any of the below criteria and selecting "List Players".
First and Last Name have assumed wildcards (ie, 'Sm' will give you Smith and Small
as well as Ismail and Riemersma).</P>

<P>Step 2: Change the 'Available' tag to 'Pick Up' for any players you are interested in.  If you only want to drop players or change your waiver priorities skip this step.</P>

<P>Step 3: Click the "Perform Transactions" button to go to the next page.</P>
<HR>

<FORM ACTION="list.php" METHOD="POST">

Last Name:<INPUT TYPE="text" NAME="Last" VALUE="<? print $Last; ?>">
First Name:<INPUT TYPE="text" NAME="First" VALUE="<? print $First; ?>"><BR>

<SELECT NAME="Position">
<OPTION VALUE=""<? if ($Position=="") print " SELECTED "; ?>>ALL Positions</OPTION>
<OPTION VALUE="HC"<? if ($Position=="HC") print " SELECTED "; ?>>Head Coaches</OPTION>
<OPTION VALUE="QB"<? if ($Position=="QB") print " SELECTED "; ?>>Quarterbacks</OPTION>
<OPTION VALUE="RB"<? if ($Position=="RB") print " SELECTED "; ?>>Runningbacks</OPTION>
<OPTION VALUE="WR"<? if ($Position=="WR") print " SELECTED "; ?>>Wide Recievers</OPTION>
<OPTION VALUE="TE"<? if ($Position=="TE") print " SELECTED "; ?>>Tight Ends</OPTION>
<OPTION VALUE="K"<? if ($Position=="K") print " SELECTED "; ?>>Kickers</OPTION>
<OPTION VALUE="OL"<? if ($Position=="OL") print " SELECTED "; ?>>Offensive Lines</OPTION>
<OPTION VALUE="DL"<? if ($Position=="DL") print " SELECTED "; ?>>Defensive Lines</OPTION>
<OPTION VALUE="LB"<? if ($Position=="LB") print " SELECTED "; ?>>Linebackers</OPTION>
<OPTION VALUE="DB"<? if ($Position=="DB") print " SELECTED "; ?>>Defensive Backs</OPTION>
</SELECT>

<SELECT NAME="Team">
<OPTION VALUE=""<? if ($Team=="") print " SELECTED "; ?>>ALL NFL Teams</OPTION>
<OPTION VALUE="ANY"<? if ($Team=="ANY") print " SELECTED "; ?>>Any</OPTION>
<OPTION VALUE="NONE"<? if ($Team=="NONE") print " SELECTED "; ?>>None</OPTION>
<OPTION VALUE="RET"<? if ($Team=="RET") print " SELECTED "; ?>>Retired</OPTION>
<OPTION VALUE="ARI"<? if ($Team=="ARI") print " SELECTED "; ?>>Arizona</OPTION>
<OPTION VALUE="ATL"<? if ($Team=="ATL") print " SELECTED "; ?>>Atlanta</OPTION>
<OPTION VALUE="BAL"<? if ($Team=="BAL") print " SELECTED "; ?>>Baltimore</OPTION>
<OPTION VALUE="BUF"<? if ($Team=="BUF") print " SELECTED "; ?>>Buffalo</OPTION>
<OPTION VALUE="CAR"<? if ($Team=="CAR") print " SELECTED "; ?>>Carolina</OPTION>
<OPTION VALUE="CHI"<? if ($Team=="CHI") print " SELECTED "; ?>>Chicago</OPTION>
<OPTION VALUE="CIN"<? if ($Team=="CIN") print " SELECTED "; ?>>Cincinnati</OPTION>
<OPTION VALUE="CLE"<? if ($Team=="CLE") print " SELECTED "; ?>>Cleveland</OPTION>
<OPTION VALUE="DAL"<? if ($Team=="DAL") print " SELECTED "; ?>>Cowgirls</OPTION>
<OPTION VALUE="DEN"<? if ($Team=="DEN") print " SELECTED "; ?>>Denver</OPTION>
<OPTION VALUE="DET"<? if ($Team=="DET") print " SELECTED "; ?>>Detroit</OPTION>
<OPTION VALUE="GB"<? if ($Team=="GB") print " SELECTED "; ?>>Green Bay</OPTION>
<OPTION VALUE="IND"<? if ($Team=="IND") print " SELECTED "; ?>>Indianapolis</OPTION>
<OPTION VALUE="HOU"<? if ($Team=="HOU") print " SELECTED "; ?>>Houston</OPTION>
<OPTION VALUE="JAC"<? if ($Team=="JAC") print " SELECTED "; ?>>Jacksonville</OPTION>
<OPTION VALUE="KC"<? if ($Team=="KC") print " SELECTED "; ?>>Kansas City</OPTION>
<OPTION VALUE="LA"<? if ($Team=="LA") print " SELECTED "; ?>>Los Angeles</OPTION>
<OPTION VALUE="MIA"<? if ($Team=="MIA") print " SELECTED "; ?>>Miami</OPTION>
<OPTION VALUE="MIN"<? if ($Team=="MIN") print " SELECTED "; ?>>Minnesota</OPTION>
<OPTION VALUE="NE"<? if ($Team=="NE") print " SELECTED "; ?>>New England</OPTION>
<OPTION VALUE="NO"<? if ($Team=="NO") print " SELECTED "; ?>>New Orleans</OPTION>
<OPTION VALUE="NYG"<? if ($Team=="NYG") print " SELECTED "; ?>>New York Giants</OPTION>
<OPTION VALUE="NYJ"<? if ($Team=="NYJ") print " SELECTED "; ?>>New York Jets</OPTION>
<OPTION VALUE="OAK"<? if ($Team=="OAK") print " SELECTED "; ?>>Oakland</OPTION>
<OPTION VALUE="PHI"<? if ($Team=="PHI") print " SELECTED "; ?>>Philadelphia</OPTION>
<OPTION VALUE="PIT"<? if ($Team=="PIT") print " SELECTED "; ?>>Pittsburgh</OPTION>
<OPTION VALUE="SD"<? if ($Team=="SD") print " SELECTED "; ?>>San Diego</OPTION>
<OPTION VALUE="SEA"<? if ($Team=="SEA") print " SELECTED "; ?>>Seattle</OPTION>
<OPTION VALUE="SF"<? if ($Team=="SF") print " SELECTED "; ?>>San Francisco</OPTION>
<OPTION VALUE="TB"<? if ($Team=="TB") print " SELECTED "; ?>>Tampa Bay</OPTION>
<OPTION VALUE="TEN"<? if ($Team=="TEN") print " SELECTED "; ?>>Tennessee</OPTION>
<OPTION VALUE="WAS"<? if ($Team=="WAS") print " SELECTED "; ?>>Washington</OPTION>
</SELECT>

<SELECT NAME="Available">
<OPTION VALUE="" <? if ($Available=="") print "SELECTED "; ?>>ALL Players</OPTION>
<OPTION VALUE="available" <? if ($Available=="available") print "SELECTED "; ?>>Available Players</OPTION>
<OPTION VALUE="taken" <? if ($Available=="taken") print "SELECTED "; ?>>Taken Players</OPTION>
</SELECT>

<INPUT TYPE="Hidden" NAME="Order" VALUE="<? print $Order; ?>">
<INPUT TYPE="Submit" NAME="submit" VALUE="List Players">
</FORM>

<TABLE>
<FORM ACTION="confirm.php" METHOD="POST">
<TR>
<TD><B><A HREF="list.php?Position=<? print $Position; ?>&Team=<? print $Team; ?>&Available=<? print $Available; ?>&Order=teamname&submit=y&Last=<? print $Last;?>&First=<? print $First;?>">Current Team</A></B></TD><TD>&nbsp;&nbsp;&nbsp;</TD>
<TD><B><A HREF="list.php?Position=<? print $Position; ?>&Team=<? print $Team; ?>&Available=<? print $Available; ?>&Order=lastname&submit=y&Last=<? print $Last;?>&First=<? print $First;?>">Last Name</A></B></TD><TD>&nbsp;&nbsp;&nbsp;</TD>
<TD><B><A HREF="list.php?Position=<? print $Position; ?>&Team=<? print $Team; ?>&Available=<? print $Available; ?>&Order=firstname&submit=y&Last=<? print $Last;?>&First=<? print $First;?>">First Name</A></B></TD><TD>&nbsp;&nbsp;&nbsp;</TD>
<TD><B><A HREF="list.php?Position=<? print $Position; ?>&Team=<? print $Team; ?>&Available=<? print $Available; ?>&Order=position&submit=y&Last=<? print $Last;?>&First=<? print $First;?>">Pos</A></B></TD><TD>&nbsp;&nbsp;&nbsp;</TD>
<TD><B><A HREF="list.php?Position=<? print $Position; ?>&Team=<? print $Team; ?>&Available=<? print $Available; ?>&Order=nflteam&submit=y&Last=<? print $Last;?>&First=<? print $First;?>">NFL Team</A></B></TD></TR>
<?
	if ($theset) {
		while (list($avail, $last, $first, $pos, $team, $id) = mysql_fetch_row($result)) {
			if ($avail == "Available") {
				print "<TR><TD><SELECT NAME=\"pick$id\"><OPTION VALUE=\"\" SELECTED>Available</OPTION><OPTION VALUE=\"$id\">Pick Up</OPTION></SELECT></TD>";
			} else {
				print "<TR><TD>$avail</TD>";
			}
			print "<TD>&nbsp;&nbsp;&nbsp;</TD><TD>$last</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD>$first</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD>$pos</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD>$team</TD></TR>";
		}
	}

?>
<TR><TD COLSPAN=9 ALIGN=Center><INPUT TYPE="Submit" NAME="submit" VALUE="Perform Transactions"></TD></TR>
</FORM>
</TABLE>

<?
} else {
?>

<CENTER><B>You must be logged in to perform transactions</B></CENTER>

<? }
include  "base/footer.html";
?>
