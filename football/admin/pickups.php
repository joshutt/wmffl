<?
require_once "utils/start.php";

class Player {
    var $name = "";
    var $pos = "";
    var $team = "";
    var $pts = array();

    function sum() {
        return array_sum($this->pts);
    }

    function games() {
        return sizeof($this->pts);
    }

    function average() {
        $avg = $this->sum()/$this->games();
        return $avg;
    }


    function median() {
        $last = $this->pts;
        sort($last);
        $num = sizeof($last);

        if ($num % 2 == 1) {
            $spot = ($num - 1) / 2;
            return $last[$spot];
        } else {
            $spot = $num/2;
            $sum = $last[$spot] + $last[$spot-1];
            return $sum/2;
        }
    }


    function adjavg() {
        $last = $this->pts;
        sort($last);
        array_pop($last);
        array_shift($last);
        $num = sizeof($last);
        $sum = array_sum($last);
        if ($num == 0) {
            return 0;
        }
        return $sum/$num;
    }

    function recentAvg() {
        $num = $this->games();
        if ($num == 1) {
            return $this->pts[0];
        } else if ($num == 2) {
            $last = $this->pts[1];
            $second = $this->pts[0];
            $avg = (2*$last + $second) / 3;
            return $avg;
        }

        $last = $this->pts[$num-1];
        $second = $this->pts[$num-2];
        $third = $this->pts[$num-3];
        $avg = (3*$last + 2*$second + $third) / 6;
        return $avg;
    }

    function recentMed() {
        if (sizeof($this->pts) <= 5) {
            return $this->median();
        }

        $num = sizeof($this->pts);
        $newArray = array($this->pts[$num-1], $this->pts[$num-2], $this->pts[$num-3], $this->pts[$num-4], $this->pts[$num-5]);
        sort($newArray);
        return $newArray[2];
    }
}

function pcmp($a, $b) {
    $aScore = $a->average() + $a->median() + $a->adjavg() + $a->recentAvg() + $a->recentMed();
    $bScore = $b->average() + $b->median() + $b->adjavg() + $b->recentAvg() + $b->recentMed();
    if ($aScore == $bScore) {
        return 0;
    }
    return ($aScore > $bScore) ? -1 : 1;
}


function printPlayer($player) {
    global $currentWeek;
    print "<tr><th colspan=\"18\">".$player->name."</th></tr>";
    print "<tr>";
    foreach ($player->pts as $pt) {
        print "<td>$pt</td>";
    }
    print "</tr>";

    $a = $player->average();
    $b = $player->median();
    $c = $player->adjavg();
    $d = $player->recentAvg();
    $e = $player->recentMed();

    print "<tr>";
    printf ("<td>%5.2f</td>",$a);
    printf ("<td>%5.2f</td>",$b);
    printf ("<td>%5.2f</td>",$c);
    printf ("<td>%5.2f</td>",$d);
    printf ("<td>%5.2f</td>",$e);
    print "<td></td>";
    printf ("<td>%5.2f</td>",$a+$b+$c+$d+$e);
    print "</tr>";

    if (sizeof($player->pts) < $currentWeek / 2) {
        $mult = sizeof($player->pts) / ($currentWeek/2);
        
        print "<tr>";
        printf ("<td>%5.2f</td>",$a*$mult);
        printf ("<td>%5.2f</td>",$b*$mult);
        printf ("<td>%5.2f</td>",$c*$mult);
        printf ("<td>%5.2f</td>",$d*$mult);
        printf ("<td>%5.2f</td>",$e*$mult);
        print "<td></td>";
        printf ("<td>%5.2f</td>",($a+$b+$c+$d+$e)*$mult);
        print "</tr>";


    }
    
    print "<tr><td></td></tr>";
}


$pos = $_REQUEST["pos"];
if ($pos == null || $pos=="") {
    $pos = 'QB';
}

$query = <<<EOD
    SELECT p.playerid, concat(p.firstname, ' ', p.lastname) as 'name', p.pos, ps.week, ps.pts, r.teamid
    FROM newplayers p, playerscores ps
    LEFT JOIN roster r ON r.playerid=p.playerid and r.dateoff is null
    WHERE p.playerid=ps.playerid
    AND p.pos='$pos' and ps.season=2007
    and p.active=1 and p.usePos=1
    order by p.playerid, ps.week
EOD;

$results = mysql_query($query) or die("Dead: ".mysql_error());

$current = 0;
$fullArray = array();
$newObj = null;
while ($row = mysql_fetch_array($results)) {
    if ($row['playerid'] != $current) {
        if ($newObj != null) {
            array_push($fullArray, $newObj);
        }
        $newObj = new Player();
        $newObj->name = $row['name'];
        $newObj->pos = $row['pos'];
        $newObj->team = $row['teamid'];
        $current = $row['playerid'];
    }

    array_push($newObj->pts, $row['pts']);
}
array_push($fullArray, $newObj);
usort($fullArray, "pcmp");

print "<table>";
print "<tr><td valign=\"top\">";
print "<table border=\"1\">";
foreach ($fullArray as $player) {
    if ($player->team != "") {
        continue;
    }

    printPlayer($player);
}
print "</table>";


print "</td><td valign=\"top\">";
print "<table border=\"1\">";
foreach ($fullArray as $player) {
    if ($player->team != "2") {
        continue;
    }

    printPlayer($player);
}
print "</table>";
print "</td>";

$teamId = $_REQUEST["team"];
if (isset($teamId) &&  $teamId != "") {
    print "<td valign=\"top\">";
    print "<table border=\"1\">";
    foreach ($fullArray as $player) {
        if ($player->team != $teamId) {
            continue;
        }

        printPlayer($player);
    }
    print "</table>";
    print "</td>";
}

print "</tr></table>";
?>
