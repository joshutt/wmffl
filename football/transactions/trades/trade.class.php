<?php
require_once 'loadTrades.inc.php';

class Team {
    var $name;
    var $id;

    function __construct($newName, $newID) {
        $this->setName($newName);
        $this->setID($newID);
    }
    function setName($newName) {$this->name=$newName;}
    function setID($newID) { $this->id = $newID; }
    function getName() {return $this->name;}
    function getID() {return $this->id;}
}

class Player {
    var $name;
    var $id;
    var $pos;
    var $nflTeam;

    function __construct($newName, $newID) {
        $this->setName($newName);
        $this->setID($newID);
    }

    function setName($newName) { $this->name = $newName; }
    function setID($newID) { $this->ID = $newID; }
    function setPos($newPos) { $this->pos = $newPos; }
    function setNFLTeam($newTeam) { $this->nflTeam = $newTeam; }
    function getName() {return $this->name;}
    function getID() {return $this->ID;}
    function getPos() {return $this->pos;}
    function getNFLTeam() {return $this->nflTeam;}
    function nicePrint() {return $this->getName()." (".$this->getPos()."-".$this->getNFLTeam().")";}
}

class Pick {
    var $season;
    var $round;
    var $orgOwner;
    
    function __construct($newSeason, $newRound, $newOrgOwn) {
        $this->setSeason($newSeason);
        $this->setRound($newRound);
        $this->setOrgOwner($newOrgOwn);
    }
    function setSeason($newSeason) {$this->season=$newSeason;}
    function setRound($newRound) {$this->round=$newRound;}
    function setOrgOwner($newOrgOwn) {$this->orgOwner=$newOrgOwn;}
    function getSeason() {return $this->season;}
    function getRound() {return $this->round;}
    function getOrgOwner() {return $this->orgOwner;}
    function nicePrint() {return $this->getSeason()." ".$this->getRound().ordinalEnding($this->getRound())." round draft pick";}
}

class Points {
    var $numPts;
    var $season;

    function __construct($newPts, $newSeason) {
        $this->setPts($newPts);
        $this->setSeason($newSeason);
    }

    function setPts($newPts) {$this->numPts=$newPts;}
    function setSeason($newSeason) {$this->season=$newSeason;}
    function getPts() {return $this->numPts;}
    function getSeason() {return $this->season;}
    function nicePrint() {return $this->getPts()." transaction point".getPlural($this->getPts())." in ".$this->getSeason();}
}

class Trade {

    var $offerID;
    var $thisTeam;
    var $otherTeam;
    var $offeredTeam;
    var $dateOffered;
    var $status;
    var $playersTo = array();
    var $playersFrom = array();
    var $picksTo = array();
    var $picksFrom = array();
    var $pointsTo = array();
    var $pointsFrom = array();

    function __construct($newID, $newStatus="Pending", $newDate="1970-01-01") {
        $this->setID($newID);
        $this->setStatus($newStatus);
        $this->setDateOffered($newDate);
    }

    function setID($newID) {$this->offerID=$newID;}
    function setStatus($newStatus) {$this->status=$newStatus;}
    function setDateOffered($newDate) {$this->dateOffered=strtotime($newDate);}
    function setOtherTeam($newTeam) {$this->otherTeam=$newTeam;}
    function setThisTeam($newTeam) {$this->thisTeam=$newTeam;}
    function setOfferedTeam($newTeam) {$this->offeredTeam=$newTeam;}
    function setPlayersTo($newPlayers) {$this->playersTo=$newPlayers;}
    function setPlayersFrom($newPlayers) {$this->playersFrom=$newPlayers;}
    function setPicksTo($newPlayers) {$this->picksTo=$newPlayers;}
    function setPicksFrom($newPlayers) {$this->picksFrom=$newPlayers;}
    function addPlayersTo($newPlayer) {array_push($this->playersTo, $newPlayer);}
    function addPlayersFrom($newPlayer) {array_push($this->playersFrom, $newPlayer);}
    function addPicksTo($newPick) {array_push($this->picksTo, $newPick);}
    function addPicksFrom($newPick) {array_push($this->picksFrom, $newPick);}
    function setPointsTo($newPts) {$this->pointsTo=$newPts;}
    function setPointsFrom($newPts) {$this->pointsFrom=$newPts;}

    function getID() {return $this->offerID;}
    function getStatus() {return $this->status;}
    function getDateOffered() {return $this->dateOffered;}
    function getDateExpires() {return $this->dateOffered+(7*24*60*60);}
    function getOtherTeam() {return $this->otherTeam;}
    function getThisTeam() {return $this->thisTeam;}
    function getOfferedTeam() {return $this->offeredTeam;}
    function getPlayersTo() {return $this->playersTo;}
    function getPlayersFrom() {return $this->playersFrom;}
    function getPicksTo() {return $this->picksTo;}
    function getPicksFrom() {return $this->picksFrom;}
    function getPointsTo() {return $this->pointsTo;}
    function getPointsFrom() {return $this->pointsFrom;}
}

