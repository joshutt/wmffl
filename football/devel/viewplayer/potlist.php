<?
class TeamHold {

    var $PotArray;
    var $ActArray;
    var $teamid;

    function TeamHold () {
        $this->PotArray = array('HC'=>array(), 'QB'=>array(), 'RB'=>array(), 'WR'=>array(), 'TE'=>array(), 'K'=>array(), 'OL'=>array(), 'DL'=>array(), 'LB'=>array(), 'DB'=>array());
        $this->ActArray = array('HC'=>array(), 'QB'=>array(), 'RB'=>array(), 'WR'=>array(),
                 'TE'=>array(), 'K'=>array(), 'OL'=>array(), 'DL'=>array(),
                 'LB'=>array(), 'DB'=>array());
    }

    function setTeamID($id) {
        $this->teamid=$id;
    }
    function getTeamID() {
        return $this->teamid;
    }
    function addPotential($pos, $score) {
#        print "Adding Potential $score to $pos<BR>";
        array_push($this->PotArray[$pos], $score);
    }
    function addActive($pos, $score) {
#        print "Adding $score to $pos<BR>";
        array_push($this->ActArray[$pos], $score);
    }
    function getOffensivePotential() {
        return $this->getOffensiveArrScore($this->PotArray);
    }
    function getOffensiveActual() {
        return $this->getOffensiveArrScore($this->ActArray);
    }
    function getDefensivePotential() {
        return $this->getDefensiveArrScore($this->PotArray);
    }
    function getDefensiveActual() {
        return $this->getDefensiveArrScore($this->ActArray);
    }
    function getPotential() {
        return $this->getOffensivePotential() + $this->getDefensivePotential();
    }
    function getActual() {
        return $this->getOffensiveActual() + $this->getDefensiveActual();
    }
    
    function getOffensiveArrScore($Prray) {
        #print_r($Prray);
        rsort($Prray['HC']);
        $total = $Prray['HC'][0];
        rsort($Prray['QB']);
        $total += $Prray['QB'][0];
        #print_r($Prray['RB']);
        rsort($Prray['RB']);
        #print_r($Prray['RB']);
        $total += $Prray['RB'][0] + $Prray['RB'][1];
        rsort($Prray['WR']);
        $total += $Prray['WR'][0] + $Prray['WR'][1];
        rsort($Prray['TE']);
        $total += $Prray['TE'][0];
        rsort($Prray['K']);
        $total += $Prray['K'][0];
        rsort($Prray['OL']);
        $total += $Prray['OL'][0];
#        print $total."<BR>";
        return $total;
    }
    function getDefensiveArrScore($Prray) {
        rsort($Prray['DL']);
        $total = $Prray['DL'][0] + $Prray['DL'][1];
        rsort($Prray['LB']);
        $total += $Prray['LB'][0] + $Prray['LB'][1];
        rsort($Prray['DB']);
        $total += $Prray['DB'][0] + $Prray['DB'][1];
        return $total;
    }
}

require_once "$DOCUMENT_ROOT/base/conn.php";

for ($iWeek = 1; $iWeek<4; $iWeek++) {
$sql = "select r.teamid, p.position, ps.pts, ps.active ";
$sql .= "from roster r, weekmap w, playerscores ps, players p ";
$sql .= "where w.season=2003 and w.week=$iWeek ";
$sql .= "and r.dateon <= w.activationdue ";
$sql .= "and (r.dateoff is null or r.dateoff >= w.activationdue) ";
$sql .= "and r.playerid=ps.playerid ";
$sql .= "and ps.season=w.season ";
$sql .= "and ps.week=w.week ";
$sql .= "and r.playerid=p.playerid ";
$sql .= "order by r.teamid, p.position ";

$results = mysql_query($sql);

$teamArr = array();
while ($player = mysql_fetch_array($results)) {
    $currentTeam = $player["teamid"];
    if (!isset($teamArr[$currentTeam])) {
        $teamArr[$currentTeam] = new TeamHold();
#        Print "Team $currentTeam is created<BR>";
        $teamArr[$currentTeam]->setTeamID($currentTeam);
    }
#    print_r ($player);
#    print "<BR>";
    if ($player["active"] != null && $player["active"] != "") {
        $teamArr[$currentTeam]->addActive($player["position"], $player["active"]);
#        print "***".$player["active"]."***".$teamArr[$currentTeam]->getActual()."***<BR>";
    }
    $teamArr[$currentTeam]->addPotential($player["position"], $player["pts"]);
}

foreach ($teamArr as $team) {
    $rating = round(($team->getActual()*2+$team->getPotential())/3, 2);
    $activePCT = round($team->getActual()/$team->getPotential()*100, 2);
    print $team->getTeamID().": ".$team->getActual()." - ".$team->getPotential().": $activePCT% - $rating<BR>";
}
}
/*
print "<P>";
foreach ($teamArr as $team) {
    foreach ($teamArr as $team2) {
        if ($team != $team2) {
            print $team->getTeamID()." vs ".$team2->getTeamID();
            print " ".($team->getOffensiveActual()-$team2->getDefensiveActual());
            print "-".($team2->getOffensiveActual()-$team->getDefensiveActual());
            print "<BR>";
        }
    }
}
*/
?>
