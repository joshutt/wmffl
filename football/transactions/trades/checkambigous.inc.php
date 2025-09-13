<?php
require_once 'base/conn.php';

$PROBLEM = true;
$NOPROBLEM = false;

class InvalidTrans {
    var int $team;
    var int $year;
    var int $pts;
}

class InvalidDraft {
    var int $team;
    var int $year;
    var int $round;
    var int $num;
}

class DuplicateDraft {
    var int $team;
    var int $year;
    var int $round;
    var array $options = array();
}

function checkTransactions(mysqli $conn, $teamid, &$retArray, $post): bool
{
    // Get each team's number
    $teamto = $post['teamto'];
    $trans = array($post['you'], $post['they']);

    // for each pts object look if given player has that many points in year
    for ($i=0; $i<count($trans); $i++) {
        $team = (($i==0) ? $teamid : $teamto);
        for ($j=0; $j<count($trans[$i]); $j++) {
            $tranAction = $trans[$i][$j];
            if (str_starts_with($tranAction, 'pts')) {
                $pts = substr($tranAction, 7, 2);
                $year = substr($tranAction, 3, 4);
                $sql = "SELECT TotalPts-(ProtectionPts+TransPts) as 'PtsLeft' ";
                $sql .= "FROM transpoints WHERE season=$year AND ";
                $sql .= "teamid=$team";
                $results = mysqli_query($conn, $sql);
                $arr = mysqli_fetch_array($results);
                if (!$arr || $arr['PtsLeft'] < $pts) {
                    $newInvalid = new InvalidTrans();
                    $newInvalid->team = $team;
                    $newInvalid->year = $year;
                    $newInvalid->pts = $pts;
                    $retArray[] = $newInvalid;
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

function checkDraft(mysqli $conn, int $teamid, &$retArray, $post): bool
{
    // Get each team's number
    $teamto = $post['teamto'];
    $trans = array($post['you'], $post['they']);

    for ($i=0; $i<count($trans); $i++) {
        $team = (($i==0) ? $teamid : $teamto);
        for ($j=0; $j<count($trans[$i]); $j++) {
            $tranAction = $trans[$i][$j];
            if (str_starts_with($tranAction, 'pick')) {
                $round = substr($tranAction, 8);
                $year = substr($tranAction, 4, 4);
                $sql = "SELECT * FROM draftpicks WHERE season=$year AND ";
                $sql .= "round=$round AND teamid=$team";
                //print "Checking $sql";
                $results = mysqli_query($conn, $sql);
                //$arr = mysqli_fetch_array($results);
                $num = mysqli_num_rows($results);
                if ($num != 1) {
                    $newInvalid = new InvalidDraft();
                    $newInvalid->team = $team;
                    $newInvalid->year = $year;
                    $newInvalid->round = $round;
                    $newInvalid->num = $num;
                    $retArray[] = $newInvalid;
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
