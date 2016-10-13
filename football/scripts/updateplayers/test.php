<?
require_once "config.php";

include_once $LIB."DateTime.php";
include $LIB."player.php";

function setUpFile($filename) {
    $handle=fopen($filename, "rb");
    //$handle=fopen($DATA_LOC."/".$filename, "rb");
    $intro = fread($handle, 16);

    $date = readint($handle);
    $updateDate = EncodeDate(1600, 1, 1)+$date;
    $intro = fread($handle, 20);
    return $handle;
}

function readint ($fp) {
   $c = ord(fgetc($fp));
   $c |= (ord(fgetc($fp)) << 8);
   $c |= (ord(fgetc($fp)) << 16);
   $c |= (ord(fgetc($fp)) << 24);
   return (int)$c;
}

function readlong ($fp) {
    $min = readint($fp);
    $max = readint($fp);
    $c = $max << 32;
    $c |= $min;
    return (int)$c;
}

function getPlayerInfo($handle, &$playerList) {
    $fieldid = readint($handle);
    $ident = ord(fgetc($handle));
    //while ($ident) {
    while ($fieldid) {
   //     print "Ident: $ident";
   //     print "*".ord($fieldid);
        //if (ord($fieldid) == 99) {
        if ($fieldid == 99) {
            $playerList[$player->getID()] = $player;
            //print "Create player ".$player->getID()."\n";
        }
        switch ($ident) {
        case 1:
            //$fieldid = readint($handle);
            $flag = ord(fgetc($handle));
            if ($fieldid == '\x18') {
                if ($flag) $active=true;
            }
            break;
        case 2:
            //$fieldid = readint($handle);
            $value = readint($handle);
            //print "Value: $value ";
            if ($fieldid == 1) {
                if (isset($playerList[$value])) {
                    $player = $playerList[$value];
                } else {
                    $player = new Player($value);
                }
                //print $player->getID()."-";
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
            //$fieldid = readint($handle);
            $length = readint($handle);
            $value = fread($handle, $length);
            if ($fieldid == 2) {
                $player->setName(trim($value));
            }
            break;
        case 4:
            $value = readlong($handle);
        }
        $fieldid = readint($handle);
        $ident = ord(fgetc($handle));
        //print "Fieldid: $fieldid - Ident: $ident ";
    }
   //print "$name - $active - $position\n";
//    return $playerList;
}


function getTransactions($handle, &$playerList) {
    $fieldid = readint($handle);
    $ident = ord(fgetc($handle));
    //while ($ident) {
    while ($fieldid) {
        if ($fieldid == 99) { 
            //print "$statid\n";
            $playerList[$statid]->addTransaction($transaction);
            $flag = ord(fgetc($handle));
        }
        switch ($ident) {
        case 2:
            $value = readint($handle);
            switch ($fieldid) {
                case 1: 
                    $statid=$value; 
//                    print "$statid-";
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
        $fieldid = readint($handle);
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

//include "updatedatabase.php";
//updateDatabase($playerList);

?>
