<?
require_once "base/conn.php";
require_once "base/useful.php";

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

putenv("TZ=US/Eastern");
if (date("w") == 2 && date("H") >= 13) {
    $thisWeek = $currentWeek - 1;
} else {
    $thisWeek = $currentWeek;
}
if ($currentWeek == 16) {$thisWeek=16;}

$gameresults = getOtherGames($thisWeek, $currentSeason, $conn);
$gameCol = 0;
$gamesPrint = array();
while ($row = mysql_fetch_array($gameresults)) {
	if ($row['scorea'] < $row['scoreb']) {
		$winName = $row['bname'];
		$winScore = $row['scoreb'];
		$loseName = $row['aname'];
		$loseScore = $row['scorea'];
	} else {
		$winName = $row['aname'];
		$winScore = $row['scorea'];
		$loseName = $row['bname'];
		$loseScore = $row['scoreb'];
	}
    	
	$myString = "<TABLE BORDER=1>";
	$myString .=  "<TR><TD><FONT SIZE=-1>$winName</FONT></TD>";
	$myString .= "<TD ALIGN=Center><FONT SIZE=-1>$winScore</TD>";
	$myString .= "<TD ROWSPAN=2 VALIGN=Center ALIGN=Center>";
	$myString .= "<A HREF=\"/activate/currentscore.php?teamid=".$row['teamA']."&week=".$thisWeek."\"><FONT SIZE=-2>";
	$myString .= "Box<BR>Score</FONT></A></TD></TR>";
	$myString .= "<TR><TD><FONT SIZE=-1>$loseName</TD>";
	$myString .= "<TD ALIGN=Center><FONT SIZE=-1>$loseScore</TD></TR>";
	$myString .= "</TABLE>";

    array_push($gamesPrint, $myString);
}


print "<TABLE BORDER=0><TR>";
$count = 0;
foreach ($gamesPrint as $item) {
    $count++;
    if ($count <= 3) {
	print "<TD COLSPAN=2 ALIGN=Center>$item</TD>";
        if ($count == 3) print "</TR><TR>";
    } else {
        print "<TD COLSPAN=3 ALIGN=Center>$item</TD>";
    }
}
print "</TR></TABLE>";
?>

