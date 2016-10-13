<?
require_once $DOCUMENT_ROOT."/base/conn.php";
include $DOCUMENT_ROOT."/base/useful.php";

class Team {
    
    var $name;
    var $division;
    var $record;
    var $divRecord;
    var $ptsFor;
    var $ptsAgt;
    var $divPtsFor;
    var $divPtsAgt;
    var $games;
    
    function Team($newName, $newDiv) {
        $this->name = $newName;
        $this->division = $newDiv;
        $this->record = array(0, 0 ,0);
        $this->divRecord = array(0,0,0);
        $this->ptsFor = 0;
        $this->ptsAgt = 0;
        $this->divPtsFor = 0;
        $this->divPtsAgt = 0;
        $this->games = array();
    }

    function addGame($opp, $ptsFor, $ptsAgt, $oppDiv) {
        array_push($this->games, array($opp, $ptsFor, $ptsAgt));
        if ($this->division == $oppDiv) {
            $this->divPtsFor += $ptsFor;
            $this->divPtsAgt += $ptsAgt;
            if ($ptsFor > $ptsAgt) {
                $this->divRecord[0]++;
            } elseif ($ptsFor < $ptsAgt) {
                $this->divRecord[1]++;
            } else {
                $this->divRecord[2]++;
            }
        }
        $this->ptsFor += $ptsFor;
        $this->ptsAgt += $ptsAgt;
        if ($ptsFor > $ptsAgt) {
            $this->record[0]++;
        } elseif ($ptsFor < $ptsAgt) {
            $this->record[1]++;
        } else {
            $this->record[2]++;
        }
    }

    function getWinPCT() {
        if ($this->record[0]+$this->record[2] == 0) {
            return 0.000;
        } else {
            return ($this->record[0] + $this->record[2]/2.0)/($this->record[0]+$this->record[1]+$this->record[2]);
        }
    }
    
    function getDivWinPCT() {
        if ($this->divRecord[0]+$this->divRecord[2] == 0) {
            return 0.000;
        } else {
            return ($this->divRecord[0] + $this->divRecord[2]/2.0)/($this->divRecord[0]+$this->divRecord[1]+$this->divRecord[2]);
        }
    }
}

function orderteam($a, $b) {
    if ($a === $b) {return 0;}
    if ($a->division < $b->division) {
        return -1;
    } elseif ($a->division > $b->division) {
        return 1;
    }
    if ($a->getWinPCT() > $b->getWinPCT()) {
        return -1;
    } elseif ($a->getWinPCT() < $b->getWinPCT()) {
        return 1;
    }
    $games = $a->games;
    $h2h = array(0,0,0,0,0);
    foreach ($games as $game) {
        if ($game[0] == $b->name) {
            $pFor = $game[1];
            $pAgt = $game[2];
            if ($pFor > $pAgt) {
                $h2h[0]++;
            } elseif ($pFor < $pAgt) {
                $h2h[1]++;
            } else {
                $h2h[2]++;
            }
            $h2h[3] += $pFor;
            $h2h[4] += $pAgt;
        }
    }
    if ($h2h[0] > $h2h[1]) {
        return -1;
    } elseif ($h2h[0] < $h2h[1]) {
        return 1;
    }

    if ($a->division == $b->division) {
        if ($a->getDivWinPCT() > $b->getDivWinPCT()) {
            return -1;
        } elseif ($a->getDivWinPCT() < $b->getDivWinPCT()) {
            return 1;
        }
    }
    if ($a->ptsFor-$a->ptsAgt > $b->ptsFor-$b->ptsAgt) {
        return -1;
    } elseif ($a->ptsFor-$a->ptsAgt < $b->ptsFor-$b->ptsAgt) {
        return 1;
    }
    if ($h2h[3] > $h2h[4]) {
        return -1;
    } elseif ($h2h[3] < $h2h[4]) {
        return 1;
    }
    return 0;
}

$sql = "select t1.name as name, t1.divisionid as division, ";
$sql .= "if (t1.teamid=s.teama, s.scorea, s.scoreb) as ptsfor, ";
$sql .= "if (t1.teamid=s.teamb, s.scorea, s.scoreb) as ptsag, ";
$sql .= "t2.name as opp, t2.divisionid as oppdiv, s.week  as week ";
$sql .= "from schedule s, team t1, team t2 ";
$sql .= "where s.season=$currentSeason and s.week<$currentWeek ";
$sql .= "and t1.teamid in (s.teama, s.teamb) ";
$sql .= "and t2.teamid in (s.teama, s.teamb) ";
$sql .= "and t1.teamid<>t2.teamid";

$teamArray = array();

$results = mysql_query($sql) or die(mysql_error());
while($games = mysql_fetch_array($results)) {
    $teamName = $games["name"];
    if (!array_key_exists($teamName, $teamArray)) {
        $teamArray[$teamName] = new Team($teamName, $games["division"]);
    }
    $teamArray[$teamName]->addGame($games["opp"], $games["ptsfor"], $games["ptsag"], $games["oppdiv"]);
}

usort($teamArray, "orderteam");

print "<TABLE><TH>Burgandy Division</TH>";
print "<TR><TD WIDTH=33%>Team</TD><TD WIDTH=11%>Wins</TD><TD WIDTH=11%>Losses</TD><TD WIDTH=11% ALIGN=Center>Ties</TD><TD WIDTH=15%>PCT</TD><TD WIDTH=10%>PF</TD><TD WIDTH=*>PA</TD></TR>";
for ($i = 0; $teamArray[$i]->division == 1; $i++) {
    print "<TR><TD><A HREF=\"/teams/".str_replace(" ","",strtolower($teamArray[$i]->name)).".shtml\">";
    print $teamArray[$i]->name."</A></TD>";
    print "<TD ALIGN=CENTER>".$teamArray[$i]->record[0]."</TD>";
    print "<TD ALIGN=CENTER>".$teamArray[$i]->record[1]."</TD>";
    print "<TD ALIGN=CENTER>".$teamArray[$i]->record[2]."</TD>";
    printf ("<TD>%5.3f</TD>", $teamArray[$i]->getWinPCT());
    print "<TD>".$teamArray[$i]->ptsFor."</TD>";
    print "<TD>".$teamArray[$i]->ptsAgt."</TD></TR>";
}

print "</TABLE><BR>";
print "<TABLE><TH>Gold Division</TH>";
print "<TR><TD WIDTH=33%>Team</TD><TD WIDTH=11%>Wins</TD><TD WIDTH=11%>Losses</TD><TD WIDTH=11% ALIGN=Center>Ties</TD><TD WIDTH=15%>PCT</TD><TD WIDTH=10%>PF</TD><TD WIDTH=*>PA</TD></TR>";
for ($i = 5; $teamArray[$i]->division == 2; $i++) {
    print "<TR><TD><A HREF=\"/teams/".str_replace(" ","",strtolower($teamArray[$i]->name)).".shtml\">";
    print $teamArray[$i]->name."</A></TD>";
    print "<TD ALIGN=CENTER>".$teamArray[$i]->record[0]."</TD>";
    print "<TD ALIGN=CENTER>".$teamArray[$i]->record[1]."</TD>";
    print "<TD ALIGN=CENTER>".$teamArray[$i]->record[2]."</TD>";
    printf ("<TD>%5.3f</TD>", $teamArray[$i]->getWinPCT());
    print "<TD>".$teamArray[$i]->ptsFor."</TD>";
    print "<TD>".$teamArray[$i]->ptsAgt."</TD></TR>";
}

/*
foreach ($teamArray as $team) {
    print $team->name." - ".$team->ptsFor." ".$team->ptsAgt." ";
    printf ("%5.3f - %5.3f<BR>", $team->getWinPCT(), $team->getDivWinPCT());
}
*/
?>
