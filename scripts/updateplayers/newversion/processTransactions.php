<?
require_once "fileRead.php";
require_once "dates.php";
require_once "DataObjects/Nfltransactions.php";
require_once "DataObjects/Nflrosters.php";
#require_once "player.php";

function processTransFile($fp, $player, &$transArr) {

    $id = 0;

    skipHeader($fp);

    while (!feof($fp)) {
        list($field, $type, $result) = readRecord($fp);
        
        switch ($field) {
            case 1: 
                $id = $result;
                if (!is_a($player[$id], "DataObjects_Newplayers")) {
                    $play = new DataObjects_Newplayers;
                    $play->flmid = $id;
                    $play->find(true);
                    $player[$id] = $play;
                }
                $trans = new DataObjects_Nfltransactions;
                $trans->playerid = $player[$id]->playerid;
                #$player[$id][] = $trans;
                break;
            case 2:
                #$trans->transdate = getDateArray($result);
                $trans->transdate = getMysqlDate(getffDate($result));
                #$trans->transdate = getMysqlDate(getDateArray($result));
                $find = $trans->find(true);
                break;
            case 3:
                $trans->action = $trans->getActionFromNum($result);
                break;
            case 4:
                $trans->team = $trans->getTeamFromNum($result);
                break;
            case 5:
                $trans->flag = $result;
                break;
            case 99:
                //$player[$id]->addTransaction($trans);
                $transArr[] = $trans;
                break;
        }
    }
}


function saveTransactions ($transactions) {

    foreach($transactions as $trans) {
        if ($trans->N > 0) {
            $trans->update();
        } else {
            $trans->insert();
        }
    }
}


function applyTransactions ($player) {
//    for ($players as $player) {
        $trans = new DataObjects_Nfltransactions;
//        $trans->playerid = $player->playerid;
        $trans->orderBy('transdate');
        #$trans->get('playerid', $player->playerid);
        $trans->playerid = $player->playerid;
        $trans->find();
        $rosterArray = array();
        $roster = new DataObjects_Nflrosters;
        $first = true;
        $add = false;
        while ($trans->fetch()) {
            $event = $trans->getEvent();
            #print $trans->action." - ".$event."\n";
            if ($event == "ADD") {
                #print "ADD";
                if ($roster->dateoff == null && $roster->nflteamid != $trans->team && !$first) {
                    $roster->dateoff = $trans->transdate;
                    $rosterArray[] = $roster;
                    $add = false;
                }
                if ($roster->dateoff != null || $first) {
                    $roster = new DataObjects_Nflrosters;
                    $roster->playerid = $player->playerid;
                    $roster->nflteamid = $trans->team;
                    $roster->dateon = $trans->transdate;
                    $add = true;
                    $first = false;
                }
            } elseif ($event == "DROP") {
                #print "DROP";
                if ($roster->dateoff == null) {
                    $roster->dateoff = $trans->transdate;
                    $rosterArray[] = $roster;
                    $add = false;
                }
            }
        }
        if ($add) {
            array_push($rosterArray, $roster);
            /*
            if (sizeof($rosterArray) > 1) {
                print sizeof($rosterArray);
            } else {
                print ".";
            }
            */
        }
        return $rosterArray;
//    }
}

?>
