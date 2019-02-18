<?
require_once "base/conn.php";

$PROBLEM = true;
$NOPROBLEM = false;

class InvalidTrans {
    var $team;
    var $year;
    var $pts;
}

class InvalidDraft {
    var $team;
    var $year;
    var $round;
    var $num;
}

class DuplicateDraft {
    var $team;
    var $year;
    var $round;
    var $options = array();
}

function checkTransactions($teamid, &$retArray, $_POST)
{
    // Get each team's number
    $teamto = $_POST["teamto"];
    $trans = array($_POST["you"], $_POST["they"]);

    // for each pts object look if given player has that many points in year
    for ($i=0; $i<count($trans); $i++) {
        $team = (($i==0) ? $teamid : $teamto);
        for ($j=0; $j<count($trans[$i]); $j++) {
            $tranAction = $trans[$i][$j];
            if (substr($tranAction, 0, 3) == "pts") {
                $pts = substr($tranAction, 7, 2);
                $year = substr($tranAction, 3, 4);
                $sql = "SELECT TotalPts-(ProtectionPts+TransPts) as 'PtsLeft' ";
                $sql .= "FROM transpoints WHERE season=$year AND ";
                $sql .= "teamid=$team";
                $results = mysql_query($sql);
                $arr = mysql_fetch_array($results);
                if (!$arr || $arr["PtsLeft"] < $pts) {
                    $newInvalid = new InvalidTrans();
                    $newInvalid->team = $team;
                    $newInvalid->year = $year;
                    $newInvalid->pts = $pts;
                    array_push($retArray, $newInvalid);
                }
            }
        }
    }
    if (count($retArray) > 0) {
        return true; // There are errors
    } else {
        return false; // no error
    }
}

function checkDraft($teamid, &$retArray, $_POST)
{
    // Get each team's number
    $teamto = $_POST["teamto"];
    $trans = array($_POST["you"], $_POST["they"]);

    for ($i=0; $i<count($trans); $i++) {
        $team = (($i==0) ? $teamid : $teamto);
        for ($j=0; $j<count($trans[$i]); $j++) {
            $tranAction = $trans[$i][$j];
            if (substr($tranAction, 0, 4) == "pick") {
                $round = substr($tranAction, 8);
                $year = substr($tranAction, 4, 4);
                $sql = "SELECT * FROM draftpicks WHERE season=$year AND ";
                $sql .= "round=$round AND teamid=$team";
                //print "Checking $sql";
                $results = mysql_query($sql);
                //$arr = mysql_fetch_array($results);
                $num = mysql_num_rows($results);
                if ($num != 1) {
                    $newInvalid = new InvalidDraft();
                    $newInvalid->team = $team;
                    $newInvalid->year = $year;
                    $newInvalid->round = $round;
                    $newInvalid->num = $num;
                    array_push($retArray, $newInvalid);
                }
            }
        }
    }
    if (count($retArray) > 0) {
        return true; // There are errors
    } else {
        return false; // no errors
    }
}
?>
