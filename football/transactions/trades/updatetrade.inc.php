<?
function checkDups(&$playArr, &$ptsArr, $pickArr) {
    $ptsAnArray = array();
    for ($i=0; $i<sizeof($ptsArr); $i++) {
        if (!isset($ptsAnArray[$ptsArr[$i]->getSeason()])) {
            $ptsAnArray[$ptsArr[$i]->getSeason()] = 0;
        }
        $ptsAnArray[$ptsArr[$i]->getSeason()] += $ptsArr[$i]->getPts();
    }
    $arrPts = array();
    foreach ($ptsAnArray as $season=>$pts) {
        array_push($arrPts, new Points($pts, $season));
    }
    $ptsArr = $arrPts;

    //print_r ($playArr);
    //print "<br/>";
    $arrPlay = array();
    for ($i=0; $i<sizeof($playArr); $i++) {
        if ($playArr[$i] == null) {continue;}
        array_push($arrPlay, $playArr[$i]);
        //print "<br/>";
        //print_r($playArr[$i]);
        $checkID = $playArr[$i]->getID();
        //print "<br/>";
        //print $checkID;
        for ($j=$i+1; $j<sizeof($playArr); $j++) {
            //print "<br/>";
            //print_r($playArr[$j]);
            if ($playArr[$j] != null && $playArr[$j]->getID() == $checkID) {
                // Remove player from j, how??
                $playArr[$j] = null;
            }
        }
    }
    $playArr = $arrPlay;
}


$trFrPlayArr = array();
$trFrPtsArr = array();
$trFrPickArr = array();
//print "YOu: ";
//print_r ($you);
//print "<br/>";
foreach ($you as $ch) {
    if (substr($ch, 0, 4) == "play") {
        $trPlayID = substr($ch, 4);
  //      print "$trPlayID<br/>";
        array_push($trFrPlayArr, loadPlayer($trPlayID));
    } else if (substr($ch, 0, 4) == "pick") {
        $trYear = substr($ch, 4, 4);
        $trRnd = substr($ch, 8);
        array_push($trFrPickArr, new Pick($trYear, $trRnd, 0));
    } else if (substr($ch, 0, 3) == "pts") {
        $trYear = substr($ch, 3, 4);
        $trPts = substr($ch, 7);
        array_push($trFrPtsArr, new Points($trPts, $trYear));
    } else if (substr($ch, 0, 5) == "draft") {
        $drID = substr($ch, 5, 1);
        $trYear = $HTTP_POST_VARS["youdraftyear".$drID];
        $trRnd = $HTTP_POST_VARS["youdraftround".$drID];
        array_push($trFrPickArr, new Pick($trYear, $trRnd, 0));
    } else if (substr($ch, 0, 7) == "newprot") {
        $drID = substr($ch, 7, 1);
        $trPts = $HTTP_POST_VARS["you$ch"];
        $trYear = $HTTP_POST_VARS["youprotyear".$drID];
        array_push($trFrPtsArr, new Points($trPts, $trYear));
    }
}
checkDups($trFrPlayArr, $trFrPtsArr, $trFrPickArr);


$trToPlayArr = array();
$trToPtsArr = array();
$trToPickArr = array();
foreach ($they as $ch) {
    if (substr($ch, 0, 4) == "play") {
        $trPlayID = substr($ch, 4);
        array_push($trToPlayArr, loadPlayer($trPlayID));
    } else if (substr($ch, 0, 4) == "pick") {
        $trYear = substr($ch, 4, 4);
        $trRnd = substr($ch, 8);
        array_push($trToPickArr, new Pick($trYear, $trRnd, 0));
    } else if (substr($ch, 0, 3) == "pts") {
        $trYear = substr($ch, 3, 4);
        $trPts = substr($ch, 7);
        array_push($trToPtsArr, new Points($trPts, $trYear));
    } else if (substr($ch, 0, 5) == "draft") {
        $drID = substr($ch, 5, 1);
        $trYear = $HTTP_POST_VARS["theydraftyear".$drID];
        $trRnd = $HTTP_POST_VARS["theydraftround".$drID];
        array_push($trToPickArr, new Pick($trYear, $trRnd, 0));
    } else if (substr($ch, 0, 7) == "newprot") {
        $drID = substr($ch, 7, 1);
        $trPts = $HTTP_POST_VARS["they$ch"];
        $trYear = $HTTP_POST_VARS["theyprotyear".$drID];
        array_push($trToPtsArr, new Points($trPts, $trYear));
    }
}
checkDups($trToPlayArr, $trToPtsArr, $trToPickArr);

$trade->setPicksFrom($trFrPickArr);
$trade->setPointsFrom($trFrPtsArr);
$trade->setPlayersFrom($trFrPlayArr);
$trade->setPicksTo($trToPickArr);
$trade->setPointsTo($trToPtsArr);
$trade->setPlayersTo($trToPlayArr);
?>
