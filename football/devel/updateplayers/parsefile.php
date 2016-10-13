<?
require_once "config.php";

include_once $LIB."DateTime.php";
include $LIB."player.php";

function setUpFile($filename) {
    $handle=fopen($filename, "rb");
    //$handle=fopen($DATA_LOC."/".$filename, "rb");
    $intro = fread($handle, 36);

    $date = readlong($handle);
    $updateDate = EncodeDate(1600, 1, 1)+$date;
    return $handle;
}

function readlong ($fp) {
   $c = ord(fgetc($fp));
   $c |= (ord(fgetc($fp)) << 8);
   $c |= (ord(fgetc($fp)) << 16);
   $c |= (ord(fgetc($fp)) << 24);
   return (int)$c;
}


function getPlayerInfo($handle, &$playerList) {
    $ident = ord(fgetc($handle));
    while ($ident) {
        //print "Ident: $ident";
        switch ($ident) {
        case 99: 
    //	    print "$statid - $name - $yearret - $position - $draftedby\n";
            $playerList[$player->getID()] = $player;
    //	    $name="";
    //	    $yearret = 0;;
    //	    $position=0;
    //	    $statid=0;
    //	    $draftedby=0;
            break;
        case 1:
            $fieldid = readlong($handle);
            $flag = ord(fgetc($handle));
            if ($fieldid == '\x18') {
                if ($flag) $active=true;
            }
            break;
        case 2:
            $fieldid = readlong($handle);
            $value = readlong($handle);
            if ($fieldid == 1) {
                if (isset($playerList[$value])) {
                    $player = $playerList[$value];
                } else {
                    $player = new Player($value);
                }
    //		$statid = $value;
            } else if ($fieldid == 5) {
                $player->setPosition($value);
    //		$position = $value;
            } else if ($fieldid == 11) {
                $player->setYearRetired($value);
    //		$yearret = $value;
            } else if ($fieldid == 20) {
                $player->setTeam($value);
    //		$draftedby = $value;
            }
            break;
        case 3:
            $fieldid = readlong($handle);
            $length = readlong($handle);
            $value = fread($handle, $length);
            if ($fieldid == 2) {
                $player->setName(trim($value));
            }
            break;
        }
        $ident = ord(fgetc($handle));
    }
    //print "$name - $active - $position\n";
//    return $playerList;
}


function getTransactions($handle, &$playerList) {
    $ident = ord(fgetc($handle));
    while ($ident) {
        switch ($ident) {
        case 99: 
            $playerList[$statid]->addTransaction($transaction);
            break;
        case 2:
            $fieldid = readlong($handle);
            $value = readlong($handle);
            switch ($fieldid) {
                case 1: 
                    $statid=$value; 
                    $transaction = new Transaction();
                    break;
                case 2: 
                    $date=EncodeDate(1600, 1, 1)+$value; 
                    $transaction->setDate($date);
                    break;
                case 3: 
                    switch ($value) {
                        case 1: $action = "Picked Up"; break;
                        case 2: $action = "Dropped"; break;
                        case 3: $action = "IR"; break;
                        case 7: $action = "Trade"; break;
                        default: $action = "Unknown";
                    }
                    $transaction->setEvent($value);
                    break;
                case 4: 
                    $transaction->setTeam($value); 
                    break;
            }
        }
        $ident = ord(fgetc($handle));
    }
//    return $playerList;
}



// Read in base player info
//$handle=setUpFile($DATA_LOC."/play2003.nfl");
$handle=setUpFile($base_players);
$playerList = array();
getPlayerInfo($handle, $playerList);
fclose($handle);

// Read in update player info
$handle=setUpFile($delta_players);
getPlayerInfo($handle, $playerList);
fclose($handle);

// Read in base transaction info
$handle=setUpFile($base_transactions);
getTransactions($handle, $playerList);
fclose($handle);

// Read in base transaction info
$handle=setUpFile($delta_transaction);
getTransactions($handle, $playerList);
fclose($handle);

include "updatedatabase.php";
updateDatabase($playerList);
?>
