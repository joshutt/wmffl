<?php
/**
 * @var $currentSeason int
 * @var $isin bool
 * @var $conn mysqli
 * @var $entityManager Doctrine\ORM\EntityManager
 */

use App\Entity\DraftPick;
use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;

require_once "utils/start.php";
//require_once "bootstrap.php";

//require "DataObjects/Config.php";
require "clock.class.php";

// --- EntityManager Selection Logic ---
// When this script is called via the LegacyBridge, a Symfony-managed
// EntityManager will be available as `$symEntityManager`. We must use it
// to ensure the correct repository class is instantiated.
// Otherwise, we fall back to the legacy `$entityManager` from bootstrap.php.
if (isset($symEntityManager) && $symEntityManager instanceof EntityManagerInterface) {
    $entityManager = $symEntityManager;
}

// Use Doctrine to get all draft picks for the current season, ordered by round and then pick
$draftPicksRepository = $entityManager->getRepository(DraftPick::class);
$draftPicksResult = $draftPicksRepository->findBy(['season' => $currentSeason], ['round' => 'ASC', 'pick' => 'ASC']);

//$config = new DataObjects_Config;


// Get the time the clock started
$configRepository = $entityManager->getRepository(Config::class);
/** @var Config $configEntity */
$configEntity = $configRepository->findOneBy(['name' => 'draft.clock.start']);
$startClock = $configEntity?->getValue();

// Get the time the draft actually started
$configEntity = $configRepository->findOneBy(['name' => 'draft.full.start']);
$draftStart = $configEntity?->getValue();

$body = "";
$nextPick = "";
$onDeckPick = "";
$lastPick = null;
$maxPick = 0;
$pickList = array();
$round = 0;
$roundArray = array();
$pick = 0;

$lastCompletedPick = null;
/** @var DraftPick $draftPickObj */
foreach ($draftPicksResult as $draftPickObj) {
    // Determine and set-up round and pick
//    $roundDist = sprintf("%02d", $draftPickObj->getRound());
//    $pickDist = sprintf("%02d", $draftPickObj->getPick());
    $roundDist = $draftPickObj->getRound();
    $pickDist = $draftPickObj->getPick();
    if ($roundDist != $round) {
        if ($round != 0) {
            $pickList[$round] = $roundArray;
        }
        $round = $roundDist;
        $roundArray = array();
    }

    // Get Team info
    $team = $draftPickObj->getTeam();
    #$teamName = sprintf("%20.20s", $team->Name);
    $teamName = $team->getName();
    $teamId = $team->getId();

    // Get player and pick info
    $player = $draftPickObj->getPlayer();
    $pickTime = $draftPickObj->getPickTime();
    $timeStamp = null;
    if ($pickTime) {
        $timeStamp = $pickTime->getTimestamp();
        if ($timeStamp > $maxPick) {
            $maxPick = $timeStamp;
        }
    }
    
    if ($player != null) {
        $posValue = $player->getPos()?->value ?? '';
        $playerName = $player->getFirstname().' '.$player->getLastname().' ('.$posValue.'-'.$player->getTeam().')';
        $playerId = $player->getFlmid();
        $playerPos = $player->getPos();
        $playerTeam = $player->getTeam();

        $playerArray = array( "id"=> $playerId, "pos"=>$playerPos, "team"=>$playerTeam, "name"=>$playerName);
        $franchise = array( "id"=> $teamId, "name"=>$teamName);
        $draftPick = array( "round"=>$roundDist, "pick"=>$pickDist, "timestamp"=>$timeStamp, "player"=> $playerArray, "franchise"=>$franchise);
        $lastCompletedPick = $draftPick;
    } else {
        //$startClock = strtotime($startClock);
        if ($startClock > $maxPick) {
            $maxPick = $startClock;
        }

        if ($nextPick == "") {
            // Determine time of expire
            $diff = getTimeAvail($conn, $teamId) - getTotalTimeUsed($conn, $currentSeason, $roundDist, $pickDist);
            if ($diff < 0) {
                $diff = 0;
            }
            $nextPick = array( "round"=>$roundDist, "pick"=>$pickDist, "time"=>$diff, "max"=>$maxPick, "start"=>$startClock, "rtime"=>time(), "team"=>$teamName, "teamid"=>$teamId);
            $lastPick = $lastCompletedPick;
        } else if ($onDeckPick == "") {
            $onDeckFranchise = array("id"=>$teamId, "name"=>$teamName);
            $onDeckPick = array("team"=>$teamName, "round"=>$roundDist, "pick"=>$pickDist, "franchise"=>$onDeckFranchise);
        }
        $franchise = array( "id"=> $teamId, "name"=>$teamName);
        $draftPick = array( "round"=>$roundDist, "pick"=>$pickDist, "franchise"=>$franchise, "player"=>null);
    }
    $roundArray[$pickDist] = $draftPick;
}
$pickList[$round] = $roundArray;


$configEntity = $configRepository->findOneBy(['name' => 'draft.clock.run']);
$pausedValue = $configEntity?->getValue();
if ($pausedValue == "true") {
    $pausedValue = "false";
} else {
    $pausedValue = "true";
}

//exit();

//$preArray = array();
//if ($isin) {
//    $sql = "SELECT CONCAT(p.lastname, ', ', p.firstname, ' - ', p.pos, ' - ', r.nflteamid)
//    FROM draftPickHold d JOIN newplayers p ON d.playerid=p.playerid
//    JOIN nflrosters r on r.playerid=d.playerid and r.dateoff is null
//    WHERE d.teamid=$teamnum";
//    $result2 = mysqli_query($conn, $sql);
//    // Use mysqli_fetch_all to get all held players, not just the first one.
//    $preArray = mysqli_fetch_all($result2, MYSQLI_ASSOC);
//}


$draftResults = array( "timestamp"=> $maxPick, "draftstart"=>$draftStart, "paused"=> $pausedValue, "picks" => $pickList, "nextPick" => $nextPick);
$draftResults["onDeckPick"] = $onDeckPick;
$draftResults["lastPick"] = $lastPick;
//$draftResults["preArray"] = $preArray;

header("Content-type: text/x-json");
print json_encode($draftResults);
