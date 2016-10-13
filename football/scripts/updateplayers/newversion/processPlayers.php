<?
require_once "fileRead.php";
require_once "dates.php";
require_once "DataObjects/Newplayers.php";

function processPlayerFile($fp, &$player) {

    $id = 0;

    skipHeader($fp);

    while (!feof($fp)) {
        list($field, $type, $result) = readRecord($fp);
        
        switch ($field) {
            case 1: 
                $id = $result;
                if (!is_a($player[$id], "DataObjects_Newplayers")) {
                    $player[$id] = new DataObjects_Newplayers;
                    $player[$id]->flmid = $result;
                    $player[$id]->find(true);
                }
                break;
            case 2:
                $fullname = split(",", $result);
                $player[$id]->lastname = trim($fullname[0]);
                $player[$id]->firstname = trim($fullname[1]);
                break;
            case 5:
                $player[$id]->pos = $player[$id]->getPosFromNum($result);
                break;
            case 7:
                $player[$id]->team = $player[$id]->getTeamFromNum($result);
                break;
            case 10:
                $player[$id]->number = $result;
                break;
            case 11:
                $player[$id]->retired = $result;
                break;
            case 12:
                $player[$id]->height = $result;
                break;
            case 13:
                $player[$id]->weight = $result;
                break;
            case 14:
                $player[$id]->dob = getMysqlDate(getffDate($result));
                $player[$id]->check = $result;
                //$player[$id]->dob = getDateArray($result);
                break;
            case 20:
                $player[$id]->draftTeam = $player[$id]->getTeamFromNum($result);
                break;
            case 21:
                $player[$id]->draftYear = $result;
                break;
            case 22:
                $player[$id]->draftRound = $result;
                break;
            case 23:
                $player[$id]->draftPick = $result;
                break;
            case 24:
                $player[$id]->active = $result;
                break;
            case 28:
                $player[$id]->nflid = $result;
                break;

        }
    }

}


function savePlayers(&$players) {

    foreach($players as $player) {
        if ($player->N > 0) {
            $player->update();
        } else {
            $player->insert();
        }
    }
}

?>
