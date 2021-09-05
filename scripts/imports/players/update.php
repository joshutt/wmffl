<?php
require_once dirname(__FILE__)."/../../base.php";
require_once 'playerHelpers.php';
require_once 'savePlayers.php';


$season = 2021;
$request_url = "https://api.myfantasyleague.com/$season/export?TYPE=players&DETAILS=1";

//testRun($request_url);
run($request_url);



function loadPlayers($url, &$timestamp) {
	//$xml = simplexml_load_file($url."&SINCE=1380968340");
	//$timestamp =1505312780;
	$xml = simplexml_load_file($url."&SINCE=".$timestamp);
	//$xml = simplexml_load_file($url);
	
	// If error exit now
	if ($xml->getName() == "error") {
	    return null;
	}

	$players = array();
	foreach ($xml->children() as $player) {
	    $p = Player::buildFromArray($player);
	    //var_dump($p);
    //    quickPlayerDump($p);
        $players[] = $p;
	}
	
	$timestamp = null;
	foreach ($xml->attributes() as $attr => $val) {
	    if ($attr == "timestamp") {
	        $timestamp = $val;
	    }
	}
	
	return $players;
}

function quickPlayerDump(Player $player) {
    print "<tr><td>{$player->statId}</td><td>{$player->firstName}</td><td>{$player->lastName}</td><td>{$player->pos}</td><td>{$player->team}</td>";
//    print "<td>{$player->getDisplayHeight()}</td><td>{$player->weight}</td><td>{$player->status}</td><td>{$player->getDisplayBirthday()}</td>";
//    print "<td>{$player->draftInfo->year}</td><td>{$player->draftInfo->team}</td><td>{$player->draftInfo->round}</td><td>{$player->draftInfo->pick}</td>";
//    print "<td>{$player->number}</td><td>{$player->college}</td>";
    foreach($player->extRefs->get_keys() as $keyname) {
        if ($player->extRefs->get_ref($keyname) != "") {
	        print "<td>$keyname = {$player->extRefs->get_ref($keyname)}</td>";
        }
    }   
    print "</tr>";
}


function testRun($url) {
	print "<table border=\"1\">";
	print "<tr><th>Id</th><th>First Name</th><th>Last Name</th><th>Pos</th><th>Team</th>";
	//print "<th>Height</th><th>Weight</th><th>Status</th><th>Birthday</th>";
	//print "<th>Draft Year</th><th>Draft Team</th><th>Draft Rd</th><th>Draft Pk</th>";
	//print "<th>Number</th><th>College</th>";
	print "</tr>";
	
    run($url);
    
	print "</table>";
}


function run($url) {
    $lastTs = getLastTimestamp();
    
    printLogMessage();
    
    $players = loadPlayers($url,$lastTs);
    if ($players == null) {
        print "No Changes\n";
        printCloseLogMessage();
        exit();
    }
    
    //print $lastTs;
    //print "<br/>";
    //print sizeof($players);
    //evaluatePlayer($players[500]);
    //evaluatePlayer($players[1000]);
    //evaluatePlayer($players[2130]);
    //evaluatePlayer($players[2239]);
    
    foreach ($players as $player) {
        evaluatePlayer($player);
    }
    
    printCloseLogMessage();
    updateTimestamp($lastTs - 24*60*60);
}


function printLogMessage () {
    // Someday this should be a true logging type message
    print "=====================================\n";
    print date("F j, Y  g:i a"); 
    print "\n-------------------------------------\n";
}

function printCloseLogMessage() {
    print "=====================================\n";
    print "\n";
}
