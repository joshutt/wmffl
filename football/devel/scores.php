<?
require_once "$DOCUMENT_ROOT/base/conn.php";
require_once "$DOCUMENT_ROOT/base/useful.php";

function getOtherGames($thisWeek, $thisSeason, $conn) {
    $getTeamSQL = "SELECT s.teamA, s.teamB, ta.name as 'aname', tb.name as 'bname', ";
	$getTeamSQL .= "s.scorea, s.scoreb ";
	$getTeamSQL .= "FROM schedule s, team ta, team tb ";
    $getTeamSQL .= "WHERE s.Week=$thisWeek AND s.Season=$thisSeason ";
	$getTeamSQL .= "AND s.teama=ta.teamid and s.teamb=tb.teamid ";
    $results = mysql_query($getTeamSQL, $conn) or die("AUUGH: ".mysql_error());
	return $results;
//    $row = mysql_fetch_array($results);
//    return $row;
}

$gameresults = getOtherGames($currentWeek, $currentSeason, $conn);
$gameCol = 0;
$gamesPrint = array();
while ($row = mysql_fetch_array($gameresults)) {
    	
	$myString = "<TABLE>";
	$myString .=  "<TR><TD><FONT SIZE=-1>".$row['aname']."</FONT></TD>";
	$myString .= "<TD ALIGN=Center><FONT SIZE=-1>".$row['scorea']."</TD>";
	$myString .= "<TD ROWSPAN=2 VALIGN=Center ALIGN=Center>";
	$myString .= "<A HREF=\"/activate/currentscores.php?teamid=".$row['teamA']."\"><FONT SIZE=-2>";
	$myString .= "Box<BR>Score</FONT></A></TD></TR>";
	$myString .= "<TR><TD><FONT SIZE=-1>".$row['bname']."</TD>";
	$myString .= "<TD ALIGN=Center><FONT SIZE=-1>".$row['scoreb']."</TD></TR>";
	$myString .= "</TABLE>";

    array_push($gamesPrint, $myString);
}


print "<TABLE BORDER=1><TR>";
foreach ($gamesPrint as $item) {
    print "<TD>$item</TD>";
}
print "</TR></TABLE>";
?>

