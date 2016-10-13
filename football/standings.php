<?
require_once "utils/start.php";

include "lib/Team.php";

function printStandings($divName, $divID, $teamArray, $startPt) {

    $tagArray = array ("Bitch Better Have My Money"=>"z-", "Lindbergh Baby Casserole"=>"",
        "Werewolves"=>"x-", "Sacks on the Beach"=>"x-", "Norsemen"=>"",
        "Crusaders"=>"", "Rednecks"=>"", "MeggaMen"=>"",
        "Whiskey Tango"=>"", "Gallic Warriors"=>"");

    print "<TABLE><TH>$divName Division</TH>";
    print "<TR><TD WIDTH=33%>Team</TD><TD WIDTH=11%>Wins</TD><TD WIDTH=11%>Losses</TD><TD WIDTH=11% ALIGN=Center>Ties</TD><TD WIDTH=15%>PCT</TD><TD WIDTH=10%>PF</TD><TD WIDTH=*>PA</TD></TR>";
    for ($i = $startPt; $teamArray[$i]->division == $divID; $i++) {
        print "<TR><TD>";
        print $tagArray[$teamArray[$i]->name];
        print "<A HREF=\"/teams/teamroster.php?viewteam=".$teamArray[$i]->teamid."\">";
        //print "<A HREF=\"/teams/".str_replace(" ","",strtolower($teamArray[$i]->name)).".shtml\">";
        print $teamArray[$i]->name."</A></TD>";
        print "<TD ALIGN=CENTER>".$teamArray[$i]->record[0]."</TD>";
        print "<TD ALIGN=CENTER>".$teamArray[$i]->record[1]."</TD>";
        print "<TD ALIGN=CENTER>".$teamArray[$i]->record[2]."</TD>";
        printf ("<TD>%5.3f</TD>", $teamArray[$i]->getWinPCT());
        print "<TD>".$teamArray[$i]->ptsFor."</TD>";
        print "<TD>".$teamArray[$i]->ptsAgt."</TD></TR>";
    }

    print "</TABLE><BR>";

}

$sql = "select tn1.name as name, t1.divisionid as division, ";
$sql .= "if (t1.teamid=s.teama, s.scorea, s.scoreb) as ptsfor, ";
$sql .= "if (t1.teamid=s.teamb, s.scorea, s.scoreb) as ptsag, ";
$sql .= "tn2.name as opp, t2.divisionid as oppdiv, s.week  as week, ";
$sql .= "t1.teamid as teamid ";
$sql .= "from schedule s, team t1, team t2, teamnames tn1, teamnames tn2 ";
$sql .= "where s.season=$currentSeason and s.week<$currentWeek ";
$sql .= "and s.week<=14 ";
$sql .= "and t1.teamid in (s.teama, s.teamb) ";
$sql .= "and t2.teamid in (s.teama, s.teamb) ";
$sql .= "and t1.teamid<>t2.teamid ";
$sql .= "and t1.teamid=tn1.teamid and t2.teamid=tn2.teamid ";
$sql .= "and tn1.season=s.season and tn2.season=s.season ";

$teamArray = array();

$results = mysql_query($sql) or die(mysql_error());
while($games = mysql_fetch_array($results)) {
    $teamName = $games["name"];
    if (!array_key_exists($teamName, $teamArray)) {
        $teamArray[$teamName] = new Team($teamName, $games["division"], $games["teamid"]);
    }
    $teamArray[$teamName]->addGame($games["opp"], $games["ptsfor"], $games["ptsag"], $games["oppdiv"]);
}

usort($teamArray, "orderteam");


printStandings("Burgandy", 1, $teamArray, 0);
printStandings("Gold", 2, $teamArray, 5);

/*
foreach ($teamArray as $team) {
    print $team->name." - ".$team->ptsFor." ".$team->ptsAgt." ";
    printf ("%5.3f - %5.3f<BR>", $team->getWinPCT(), $team->getDivWinPCT());
}
*/
?>
<P>x- Clinched playoff berth<BR>y- Clinched division title<BR>z- Clinched Toliet Bowl berth</P>
