/*!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.25-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: joshutt_oldwmffl
-- ------------------------------------------------------
-- Server version	10.5.25-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activations`
--

DROP TABLE IF EXISTS `activations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activations` (
  `TeamID` int(11) NOT NULL DEFAULT 0,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Week` tinyint(4) NOT NULL DEFAULT 0,
  `HC` int(11) NOT NULL DEFAULT 0,
  `QB` int(11) NOT NULL DEFAULT 0,
  `RB1` int(11) NOT NULL DEFAULT 0,
  `RB2` int(11) NOT NULL DEFAULT 0,
  `WR1` int(11) NOT NULL DEFAULT 0,
  `WR2` int(11) NOT NULL DEFAULT 0,
  `TE` int(11) NOT NULL DEFAULT 0,
  `K` int(11) NOT NULL DEFAULT 0,
  `OL` int(11) NOT NULL DEFAULT 0,
  `DL1` int(11) NOT NULL DEFAULT 0,
  `DL2` int(11) NOT NULL DEFAULT 0,
  `LB1` int(11) NOT NULL DEFAULT 0,
  `LB2` int(11) NOT NULL DEFAULT 0,
  `DB1` int(11) NOT NULL DEFAULT 0,
  `DB2` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`TeamID`,`Season`,`Week`),
  UNIQUE KEY `TeamID` (`TeamID`,`Season`,`Week`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `articleId` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(75) NOT NULL DEFAULT '',
  `link` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `articleText` text DEFAULT NULL,
  `displayDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 0,
  `author` int(11) DEFAULT NULL,
  PRIMARY KEY (`articleId`)
) ENGINE=InnoDB AUTO_INCREMENT=570 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ballot`
--

DROP TABLE IF EXISTS `ballot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ballot` (
  `TeamID` int(11) NOT NULL DEFAULT 0,
  `IssueID` int(11) NOT NULL DEFAULT 0,
  `Result` tinyint(4) DEFAULT NULL,
  `Vote` enum('Accept','Reject','Abstain','No Vote') DEFAULT 'No Vote',
  PRIMARY KEY (`IssueID`,`TeamID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `messageId` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT 0,
  `message` varchar(255) NOT NULL DEFAULT '',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`messageId`)
) ENGINE=InnoDB AUTO_INCREMENT=2206 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `comments_article_id_date_created_index` (`article_id`,`date_created`),
  KEY `comments_comment_id_index` (`comment_id`),
  KEY `comments_parent_id_date_created_index` (`parent_id`,`date_created`),
  CONSTRAINT `comments_comments_comment_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `key` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `division`
--

DROP TABLE IF EXISTS `division`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `division` (
  `DivisionID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL DEFAULT '',
  `startYear` year(4) NOT NULL DEFAULT 0000,
  `endYear` year(4) NOT NULL DEFAULT 0000,
  PRIMARY KEY (`DivisionID`,`startYear`,`endYear`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `draftPickHold`
--

DROP TABLE IF EXISTS `draftPickHold`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draftPickHold` (
  `teamid` int(11) NOT NULL,
  `playerid` int(11) DEFAULT NULL,
  PRIMARY KEY (`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `draftclockstop`
--

DROP TABLE IF EXISTS `draftclockstop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draftclockstop` (
  `season` year(4) NOT NULL,
  `round` int(11) NOT NULL,
  `pick` int(11) NOT NULL,
  `timeStopped` timestamp NULL DEFAULT NULL,
  `timeStarted` timestamp NULL DEFAULT NULL,
  KEY `season` (`season`),
  KEY `season_2` (`season`,`round`,`pick`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `draftdate`
--

DROP TABLE IF EXISTS `draftdate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draftdate` (
  `UserID` int(11) NOT NULL DEFAULT 0,
  `Date` date NOT NULL DEFAULT '0000-00-00',
  `Attend` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`UserID`,`Date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `draftpicks`
--

DROP TABLE IF EXISTS `draftpicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draftpicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Round` tinyint(4) NOT NULL DEFAULT 0,
  `Pick` tinyint(4) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `orgTeam` int(11) DEFAULT NULL,
  `playerid` int(11) DEFAULT NULL,
  `pickTime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `Season` (`Season`,`Round`,`Pick`),
  UNIQUE KEY `Season_2` (`Season`,`playerid`)
) ENGINE=InnoDB AUTO_INCREMENT=4297 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `draftvote`
--

DROP TABLE IF EXISTS `draftvote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draftvote` (
  `userid` int(11) NOT NULL,
  `season` year(4) NOT NULL,
  `lastUpdate` datetime DEFAULT NULL,
  PRIMARY KEY (`userid`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expandAvailable`
--

DROP TABLE IF EXISTS `expandAvailable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expandAvailable` (
  `playerid` tinyint(4) NOT NULL,
  `teamid` tinyint(4) NOT NULL,
  `firstname` tinyint(4) NOT NULL,
  `lastname` tinyint(4) NOT NULL,
  `pos` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `cost` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expansionLost`
--

DROP TABLE IF EXISTS `expansionLost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expansionLost` (
  `teamid` int(11) NOT NULL,
  `num` int(11) NOT NULL,
  PRIMARY KEY (`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expansionpicks`
--

DROP TABLE IF EXISTS `expansionpicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expansionpicks` (
  `playerid` int(11) NOT NULL,
  `teamid` int(11) NOT NULL,
  `round` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expansionprotections`
--

DROP TABLE IF EXISTS `expansionprotections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expansionprotections` (
  `teamid` int(11) NOT NULL,
  `playerid` int(11) NOT NULL,
  `type` enum('protect','pullback','alternate') NOT NULL,
  `protected` int(11) NOT NULL,
  PRIMARY KEY (`teamid`,`playerid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum`
--

DROP TABLE IF EXISTS `forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum` (
  `forumid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`forumid`)
) ENGINE=InnoDB AUTO_INCREMENT=807 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_seq`
--

DROP TABLE IF EXISTS `forum_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gameplan`
--

DROP TABLE IF EXISTS `gameplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gameplan` (
  `gameplan_id` int(11) NOT NULL AUTO_INCREMENT,
  `season` year(4) NOT NULL,
  `week` int(11) NOT NULL,
  `teamid` int(11) NOT NULL,
  `playerid` int(11) NOT NULL,
  `side` enum('Me','Them') NOT NULL,
  PRIMARY KEY (`gameplan_id`),
  UNIQUE KEY `season` (`season`,`week`,`teamid`,`side`),
  KEY `Season_Week_Team` (`season`,`week`,`teamid`,`side`) USING BTREE KEY_BLOCK_SIZE=4
) ENGINE=InnoDB AUTO_INCREMENT=1316 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(40) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `fullImage` longblob DEFAULT NULL,
  `smallImage` blob DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_key` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `injuries`
--

DROP TABLE IF EXISTS `injuries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `injuries` (
  `playerid` int(11) NOT NULL,
  `season` year(4) NOT NULL,
  `week` int(11) NOT NULL,
  `status` enum('P','Q','D','O','I','S') NOT NULL,
  `details` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`season`,`week`,`playerid`),
  KEY `playerid` (`playerid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ir`
--

DROP TABLE IF EXISTS `ir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ir` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playerid` int(11) NOT NULL,
  `current` tinyint(1) NOT NULL DEFAULT 0,
  `dateon` datetime NOT NULL DEFAULT current_timestamp(),
  `dateoff` datetime DEFAULT NULL,
  `covid` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ir_playerid_dateon_index` (`playerid`,`dateon`),
  CONSTRAINT `ir_newplayers_playerid_fk` FOREIGN KEY (`playerid`) REFERENCES `newplayers` (`playerid`)
) ENGINE=InnoDB AUTO_INCREMENT=309 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issues` (
  `IssueID` int(11) NOT NULL AUTO_INCREMENT,
  `IssueNum` varchar(10) NOT NULL DEFAULT '',
  `IssueName` varchar(40) NOT NULL,
  `Sponsor` int(11) NOT NULL DEFAULT 0,
  `Description` tinytext DEFAULT NULL,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Deadline` date DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `Result` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`IssueID`),
  KEY `IssueID` (`IssueID`,`IssueNum`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newinjuries`
--

DROP TABLE IF EXISTS `newinjuries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newinjuries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playerid` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `week` int(11) NOT NULL,
  `status` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `details` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `expectedReturn` date DEFAULT NULL,
  `version` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newinjuries_season_week_playerid_uindex` (`season`,`week`,`playerid`),
  KEY `newinjuries_playerid_index` (`playerid`),
  CONSTRAINT `FK_newinjuries_newplayers` FOREIGN KEY (`playerid`) REFERENCES `newplayers` (`playerid`)
) ENGINE=InnoDB AUTO_INCREMENT=602903 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newplayers`
--

DROP TABLE IF EXISTS `newplayers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newplayers` (
  `playerid` int(11) NOT NULL AUTO_INCREMENT,
  `flmid` int(11) NOT NULL DEFAULT 0,
  `lastname` varchar(25) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `firstname` varchar(25) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `pos` enum('HC','QB','RB','WR','TE','K','OL','DL','LB','DB') CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `team` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `retired` year(4) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `draftTeam` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `draftYear` year(4) DEFAULT NULL,
  `draftRound` int(11) DEFAULT NULL,
  `draftPick` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `usePos` tinyint(4) NOT NULL DEFAULT 1,
  `nflid` int(11) DEFAULT NULL,
  `nfldb_id` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`playerid`),
  UNIQUE KEY `flmid` (`flmid`),
  UNIQUE KEY `unique_nfldb_id` (`nfldb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15809 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nflbyes`
--

DROP TABLE IF EXISTS `nflbyes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nflbyes` (
  `season` year(4) NOT NULL,
  `week` tinyint(4) NOT NULL,
  `nflteam` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nflgames`
--

DROP TABLE IF EXISTS `nflgames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nflgames` (
  `season` year(4) NOT NULL DEFAULT 0000,
  `week` int(11) NOT NULL DEFAULT 0,
  `homeTeam` char(3) NOT NULL DEFAULT '',
  `roadTeam` char(3) NOT NULL DEFAULT '',
  `kickoff` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `secRemain` int(11) NOT NULL DEFAULT 0,
  `complete` int(11) NOT NULL DEFAULT 0,
  `homeScore` int(11) DEFAULT NULL,
  `roadScore` int(11) DEFAULT NULL,
  PRIMARY KEY (`season`,`week`,`homeTeam`,`roadTeam`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nflrosters`
--

DROP TABLE IF EXISTS `nflrosters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nflrosters` (
  `playerid` int(11) NOT NULL DEFAULT 0,
  `nflteamid` char(3) NOT NULL DEFAULT '0',
  `dateon` date NOT NULL DEFAULT '0000-00-00',
  `dateoff` date DEFAULT NULL,
  `pos` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`playerid`,`nflteamid`,`dateon`),
  KEY `playerdate` (`playerid`,`dateon`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nflstatus`
--

DROP TABLE IF EXISTS `nflstatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nflstatus` (
  `nflteam` char(3) NOT NULL DEFAULT '',
  `season` int(11) NOT NULL DEFAULT 2002,
  `week` int(11) NOT NULL DEFAULT 0,
  `status` enum('B','P','F','L') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nflteams`
--

DROP TABLE IF EXISTS `nflteams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nflteams` (
  `nflteam` char(3) NOT NULL DEFAULT '',
  `name` varchar(25) NOT NULL DEFAULT '',
  `nickname` varchar(20) NOT NULL DEFAULT '',
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nfltransactions`
--

DROP TABLE IF EXISTS `nfltransactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nfltransactions` (
  `playerid` int(11) NOT NULL DEFAULT 0,
  `transdate` date NOT NULL DEFAULT '0000-00-00',
  `action` enum('Signed','Cut','IR','Trade','Draft','Retired','Unknown') NOT NULL DEFAULT 'Unknown',
  `team` char(3) DEFAULT NULL,
  `flag` int(11) DEFAULT NULL,
  PRIMARY KEY (`playerid`,`transdate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offer`
--

DROP TABLE IF EXISTS `offer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer` (
  `OfferID` int(11) NOT NULL AUTO_INCREMENT,
  `TeamAID` int(11) NOT NULL DEFAULT 0,
  `TeamBID` int(11) NOT NULL DEFAULT 0,
  `Status` enum('Accept','Reject','Pending','Withdrawn','Expired','Modified') DEFAULT 'Pending',
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastOfferID` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`OfferID`)
) ENGINE=InnoDB AUTO_INCREMENT=726 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offeredpicks`
--

DROP TABLE IF EXISTS `offeredpicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offeredpicks` (
  `OfferID` int(11) NOT NULL DEFAULT 0,
  `TeamFromID` int(11) NOT NULL DEFAULT 0,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Round` tinyint(4) NOT NULL DEFAULT 0,
  `OrgTeam` int(11) DEFAULT NULL,
  PRIMARY KEY (`OfferID`,`TeamFromID`,`Season`,`Round`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offeredplayers`
--

DROP TABLE IF EXISTS `offeredplayers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offeredplayers` (
  `OfferID` int(11) NOT NULL DEFAULT 0,
  `TeamFromID` int(11) NOT NULL DEFAULT 0,
  `PlayerID` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`OfferID`,`TeamFromID`,`PlayerID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offeredpoints`
--

DROP TABLE IF EXISTS `offeredpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offeredpoints` (
  `OfferID` int(11) NOT NULL DEFAULT 0,
  `TeamFromID` int(11) NOT NULL DEFAULT 0,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Points` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`OfferID`,`TeamFromID`,`Season`,`Points`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `owners`
--

DROP TABLE IF EXISTS `owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `owners` (
  `teamid` int(11) NOT NULL DEFAULT 0,
  `userid` int(11) NOT NULL DEFAULT 0,
  `season` year(4) NOT NULL DEFAULT 0000,
  `primary` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`teamid`,`userid`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paid`
--

DROP TABLE IF EXISTS `paid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teamid` int(11) DEFAULT NULL,
  `season` int(11) DEFAULT NULL,
  `previous` float DEFAULT 0,
  `entry_fee` float DEFAULT 75,
  `late_fee` float DEFAULT 0,
  `paid` tinyint(1) DEFAULT 1,
  `amtPaid` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paid_teamid_season_uindex` (`teamid`,`season`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playeroverride`
--

DROP TABLE IF EXISTS `playeroverride`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playeroverride` (
  `playerid` int(11) NOT NULL,
  `season` year(4) NOT NULL,
  `teamid` int(11) NOT NULL,
  `pos` varchar(2) NOT NULL,
  PRIMARY KEY (`playerid`,`teamid`,`season`),
  KEY `season` (`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `PlayerID` int(11) NOT NULL AUTO_INCREMENT,
  `LastName` varchar(30) NOT NULL DEFAULT '',
  `FirstName` varchar(30) NOT NULL DEFAULT '',
  `NFLTeam` varchar(5) DEFAULT NULL,
  `Position` enum('HC','QB','RB','WR','TE','K','OL','DL','LB','DB') DEFAULT NULL,
  `Status` char(1) DEFAULT NULL,
  `StatID` varchar(5) DEFAULT '0',
  PRIMARY KEY (`PlayerID`)
) ENGINE=InnoDB AUTO_INCREMENT=6357 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playerscores`
--

DROP TABLE IF EXISTS `playerscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playerscores` (
  `playerid` int(11) NOT NULL DEFAULT 0,
  `season` int(11) NOT NULL DEFAULT 0,
  `week` int(11) NOT NULL DEFAULT 0,
  `pts` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`playerid`,`season`,`week`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playerteams`
--

DROP TABLE IF EXISTS `playerteams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playerteams` (
  `playerid` int(11) NOT NULL DEFAULT 0,
  `nflteam` char(3) NOT NULL DEFAULT '',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  KEY `playerid` (`playerid`,`nflteam`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `positioncost`
--

DROP TABLE IF EXISTS `positioncost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positioncost` (
  `position` char(2) NOT NULL DEFAULT '',
  `years` int(11) NOT NULL DEFAULT 0,
  `cost` int(11) DEFAULT 0,
  `startSeason` year(4) NOT NULL,
  `endSeason` year(4) DEFAULT NULL,
  PRIMARY KEY (`position`,`years`,`startSeason`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `protectionallocation`
--

DROP TABLE IF EXISTS `protectionallocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `protectionallocation` (
  `ProtectionID` int(11) NOT NULL AUTO_INCREMENT,
  `TeamID` int(11) NOT NULL DEFAULT 0,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Special` tinyint(4) DEFAULT NULL,
  `HC` tinyint(4) NOT NULL DEFAULT 0,
  `QB` tinyint(4) NOT NULL DEFAULT 0,
  `RB` tinyint(4) NOT NULL DEFAULT 0,
  `WR` tinyint(4) NOT NULL DEFAULT 0,
  `TE` tinyint(4) NOT NULL DEFAULT 0,
  `K` tinyint(4) NOT NULL DEFAULT 0,
  `OL` tinyint(4) NOT NULL DEFAULT 0,
  `DL` tinyint(4) NOT NULL DEFAULT 0,
  `LB` tinyint(4) NOT NULL DEFAULT 0,
  `DB` tinyint(4) NOT NULL DEFAULT 0,
  `General` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ProtectionID`),
  UNIQUE KEY `ProtectionID` (`ProtectionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `protectioncost`
--

DROP TABLE IF EXISTS `protectioncost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `protectioncost` (
  `playerid` int(11) NOT NULL DEFAULT 0,
  `season` year(4) NOT NULL DEFAULT 0000,
  `years` int(11) DEFAULT 0,
  PRIMARY KEY (`playerid`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `protections`
--

DROP TABLE IF EXISTS `protections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `protections` (
  `teamid` int(11) NOT NULL DEFAULT 0,
  `playerid` int(11) NOT NULL DEFAULT 0,
  `season` year(4) NOT NULL DEFAULT 0000,
  `cost` int(11) DEFAULT NULL,
  PRIMARY KEY (`teamid`,`playerid`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rankedvote`
--

DROP TABLE IF EXISTS `rankedvote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rankedvote` (
  `issueid` int(11) NOT NULL DEFAULT 0,
  `choice` varchar(50) NOT NULL DEFAULT '',
  `teamid` int(11) NOT NULL DEFAULT 0,
  `rank` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`issueid`,`choice`,`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `revisedactivations`
--

DROP TABLE IF EXISTS `revisedactivations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `revisedactivations` (
  `season` year(4) NOT NULL DEFAULT 0000,
  `week` int(11) NOT NULL DEFAULT 0,
  `teamid` int(11) NOT NULL DEFAULT 0,
  `pos` enum('HC','QB','RB','WR','TE','K','OL','DL','LB','DB') DEFAULT NULL,
  `playerid` int(11) NOT NULL DEFAULT 0,
  KEY `season` (`season`,`week`,`teamid`),
  KEY `revisedactivations_teamid_index` (`teamid`),
  KEY `revisedactivations_season_week_playerid_index` (`season`,`week`,`playerid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roster`
--

DROP TABLE IF EXISTS `roster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roster` (
  `PlayerID` int(11) NOT NULL DEFAULT 0,
  `TeamID` int(11) NOT NULL DEFAULT 0,
  `DateOn` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateOff` datetime DEFAULT NULL,
  KEY `team_key` (`TeamID`),
  KEY `player_key` (`PlayerID`,`TeamID`),
  KEY `dateOn_key` (`DateOn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `gameid` int(11) NOT NULL AUTO_INCREMENT,
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Week` tinyint(4) NOT NULL DEFAULT 0,
  `label` varchar(255) DEFAULT NULL,
  `TeamA` int(11) NOT NULL DEFAULT 0,
  `TeamB` int(11) NOT NULL DEFAULT 0,
  `scorea` int(11) DEFAULT NULL,
  `scoreb` int(11) DEFAULT NULL,
  `overtime` tinyint(4) NOT NULL DEFAULT 0,
  `playoffs` tinyint(1) NOT NULL DEFAULT 0,
  `postseason` tinyint(1) NOT NULL DEFAULT 0,
  `championship` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`gameid`),
  KEY `Season` (`Season`,`Week`)
) ENGINE=InnoDB AUTO_INCREMENT=2678 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `season_flags`
--

DROP TABLE IF EXISTS `season_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `season_flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season` year(4) NOT NULL,
  `teamid` int(11) NOT NULL,
  `flags` varchar(3) DEFAULT NULL,
  `division_winner` tinyint(1) NOT NULL DEFAULT 0,
  `playoff_team` tinyint(1) NOT NULL DEFAULT 0,
  `finalist` tinyint(1) NOT NULL DEFAULT 0,
  `champion` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `season_team_key` (`season`,`teamid`),
  KEY `season_flags_team_TeamID_fk` (`teamid`),
  CONSTRAINT `season_flags_team_TeamID_fk` FOREIGN KEY (`teamid`) REFERENCES `team` (`TeamID`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats`
--

DROP TABLE IF EXISTS `stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats` (
  `statid` int(11) NOT NULL DEFAULT 0,
  `Season` int(11) NOT NULL DEFAULT 2002,
  `week` int(11) NOT NULL DEFAULT 0,
  `played` tinyint(4) NOT NULL DEFAULT 1,
  `yards` int(11) DEFAULT 0,
  `intthrow` int(11) DEFAULT 0,
  `rec` int(11) DEFAULT 0,
  `fum` int(11) DEFAULT 0,
  `tackles` int(11) DEFAULT 0,
  `sacks` float DEFAULT 0,
  `intcatch` int(11) DEFAULT 0,
  `passdefend` int(11) DEFAULT 0,
  `returnyards` int(11) DEFAULT 0,
  `fumrec` int(11) DEFAULT 0,
  `forcefum` int(11) DEFAULT 0,
  `tds` int(11) DEFAULT 0,
  `2pt` int(11) DEFAULT 0,
  `specTD` int(11) DEFAULT 0,
  `Safety` int(11) NOT NULL DEFAULT 0,
  `XP` int(11) NOT NULL DEFAULT 0,
  `MissXP` int(11) NOT NULL DEFAULT 0,
  `FG30` int(11) NOT NULL DEFAULT 0,
  `FG40` int(11) NOT NULL DEFAULT 0,
  `FG50` int(11) NOT NULL DEFAULT 0,
  `FG60` int(11) NOT NULL DEFAULT 0,
  `MissFG30` int(11) NOT NULL DEFAULT 0,
  `ptdiff` int(11) DEFAULT NULL,
  `blockpunt` int(11) NOT NULL DEFAULT 0,
  `blockfg` int(11) NOT NULL DEFAULT 0,
  `blockxp` int(11) NOT NULL DEFAULT 0,
  `penalties` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`statid`,`Season`,`week`),
  KEY `Season` (`Season`,`week`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team` (
  `TeamID` int(11) NOT NULL AUTO_INCREMENT,
  `DivisionID` int(11) NOT NULL DEFAULT 0,
  `Name` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `member` int(11) DEFAULT NULL,
  `statid` tinyint(4) DEFAULT NULL,
  `logo` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `fulllogo` tinyint(4) NOT NULL DEFAULT 0,
  `motto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `abbrev` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`TeamID`),
  UNIQUE KEY `Name` (`Name`),
  UNIQUE KEY `TeamID_2` (`TeamID`,`Name`),
  UNIQUE KEY `abbrev` (`abbrev`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `team_wins`
--

DROP TABLE IF EXISTS `team_wins`;
/*!50001 DROP VIEW IF EXISTS `team_wins`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `team_wins` AS SELECT
 1 AS `gameid`,
  1 AS `Season`,
  1 AS `Week`,
  1 AS `Team`,
  1 AS `Result` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `teamnames`
--

DROP TABLE IF EXISTS `teamnames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teamnames` (
  `teamid` int(11) NOT NULL DEFAULT 0,
  `season` year(4) NOT NULL DEFAULT 0000,
  `name` varchar(50) NOT NULL DEFAULT '',
  `abbrev` char(3) NOT NULL DEFAULT '',
  `divisionId` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`teamid`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `titles`
--

DROP TABLE IF EXISTS `titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `titles` (
  `season` year(4) NOT NULL DEFAULT 0000,
  `type` enum('League','Division','Toilet') NOT NULL DEFAULT 'League',
  `teamid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`season`,`type`,`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmp_players`
--

DROP TABLE IF EXISTS `tmp_players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_players` (
  `playerid` int(11) NOT NULL,
  `season` year(4) NOT NULL,
  `pts` int(11) NOT NULL,
  PRIMARY KEY (`playerid`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmp_scan`
--

DROP TABLE IF EXISTS `tmp_scan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_scan` (
  `scanId` int(11) NOT NULL AUTO_INCREMENT,
  `season` int(11) DEFAULT NULL,
  `playerid` int(11) DEFAULT NULL,
  `group` int(11) DEFAULT NULL,
  `pos` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`scanId`)
) ENGINE=InnoDB AUTO_INCREMENT=967 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trade`
--

DROP TABLE IF EXISTS `trade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trade` (
  `TradeID` int(11) NOT NULL AUTO_INCREMENT,
  `TeamFromID` int(11) NOT NULL DEFAULT 0,
  `TeamToID` int(11) NOT NULL DEFAULT 0,
  `PlayerID` int(11) DEFAULT NULL,
  `Other` text DEFAULT NULL,
  `Date` date NOT NULL DEFAULT '0000-00-00',
  `TradeGroup` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`TradeID`),
  UNIQUE KEY `TradeID` (`TradeID`)
) ENGINE=InnoDB AUTO_INCREMENT=577 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `TransactionID` int(11) NOT NULL AUTO_INCREMENT,
  `TeamID` int(11) NOT NULL DEFAULT 0,
  `PlayerID` int(11) NOT NULL DEFAULT 0,
  `Method` enum('Cut','Sign','Trade','Fire','Hire','To IR','From IR','To COVID','From COVID') NOT NULL DEFAULT 'Cut',
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`TransactionID`),
  KEY `transactions_Date_index` (`Date`)
) ENGINE=InnoDB AUTO_INCREMENT=14163 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transpoints`
--

DROP TABLE IF EXISTS `transpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transpoints` (
  `TeamID` int(11) NOT NULL DEFAULT 0,
  `season` int(11) NOT NULL DEFAULT 0,
  `ProtectionPts` int(11) NOT NULL DEFAULT 0,
  `TransPts` int(11) NOT NULL DEFAULT 0,
  `TotalPts` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`TeamID`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `TeamID` int(11) DEFAULT NULL,
  `Username` varchar(20) NOT NULL DEFAULT '',
  `Password` varchar(50) NOT NULL DEFAULT '',
  `Name` varchar(50) DEFAULT NULL,
  `Email` varchar(75) NOT NULL DEFAULT '',
  `primaryowner` tinyint(1) NOT NULL DEFAULT 0,
  `lastlog` datetime DEFAULT NULL,
  `blogaddress` varchar(75) DEFAULT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `commish` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Username_2` (`Username`),
  KEY `FK_USER_TEAM` (`TeamID`),
  CONSTRAINT `FK_USER_TEAM` FOREIGN KEY (`TeamID`) REFERENCES `team` (`TeamID`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `waiveraward`
--

DROP TABLE IF EXISTS `waiveraward`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `waiveraward` (
  `season` year(4) NOT NULL DEFAULT 0000,
  `week` tinyint(4) NOT NULL DEFAULT 0,
  `pick` tinyint(4) NOT NULL DEFAULT 0,
  `teamid` tinyint(4) NOT NULL DEFAULT 0,
  `playerid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`season`,`week`,`pick`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `waiverorder`
--

DROP TABLE IF EXISTS `waiverorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `waiverorder` (
  `season` year(4) NOT NULL DEFAULT 0000,
  `week` int(11) NOT NULL DEFAULT 0,
  `ordernumber` int(11) NOT NULL DEFAULT 0,
  `teamid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`season`,`week`,`ordernumber`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `waiverpicks`
--

DROP TABLE IF EXISTS `waiverpicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `waiverpicks` (
  `teamid` int(11) NOT NULL DEFAULT 0,
  `season` year(4) NOT NULL DEFAULT 0000,
  `week` int(11) NOT NULL DEFAULT 0,
  `playerid` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`teamid`,`season`,`week`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `weeklyplayerscores`
--

DROP TABLE IF EXISTS `weeklyplayerscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weeklyplayerscores` (
  `playerid` tinyint(4) NOT NULL,
  `name` tinyint(4) NOT NULL,
  `pos` tinyint(4) NOT NULL,
  `nflteam` tinyint(4) NOT NULL,
  `teamid` tinyint(4) NOT NULL,
  `teamname` tinyint(4) NOT NULL,
  `season` tinyint(4) NOT NULL,
  `week` tinyint(4) NOT NULL,
  `pts` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `weekmap`
--

DROP TABLE IF EXISTS `weekmap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weekmap` (
  `Season` year(4) NOT NULL DEFAULT 0000,
  `Week` int(11) NOT NULL DEFAULT 0,
  `StartDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `EndDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ActivationDue` datetime DEFAULT NULL,
  `DisplayDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `weekname` varchar(22) DEFAULT NULL,
  PRIMARY KEY (`Season`,`Week`),
  KEY `date_index` (`StartDate`,`EndDate`),
  KEY `enddate_idx` (`EndDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `years`
--

DROP TABLE IF EXISTS `years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `years` (
  `season` year(4) NOT NULL,
  PRIMARY KEY (`season`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `team_wins`
--

/*!50001 DROP VIEW IF EXISTS `team_wins`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`joshutt`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `team_wins` AS select `s`.`gameid` AS `gameid`,`s`.`Season` AS `Season`,`s`.`Week` AS `Week`,if(`s`.`scorea` > `s`.`scoreb`,`s`.`TeamA`,`s`.`TeamB`) AS `Team`,'W' AS `Result` from (`schedule` `s` join `weekmap` `wm` on(`s`.`Season` = `wm`.`Season` and `s`.`Week` = `wm`.`Week`)) where `s`.`scorea` <> `s`.`scoreb` and current_timestamp() > `wm`.`EndDate` union select `s`.`gameid` AS `gameid`,`s`.`Season` AS `Season`,`s`.`Week` AS `Week`,if(`s`.`scorea` < `s`.`scoreb`,`s`.`TeamA`,`s`.`TeamB`) AS `Team`,'L' AS `L` from (`schedule` `s` join `weekmap` `wm` on(`s`.`Season` = `wm`.`Season` and `s`.`Week` = `wm`.`Week`)) where `s`.`scorea` <> `s`.`scoreb` and current_timestamp() > `wm`.`EndDate` union select `s`.`gameid` AS `gameid`,`s`.`Season` AS `Season`,`s`.`Week` AS `Week`,`t`.`TeamID` AS `Team`,'T' AS `T` from ((`schedule` `s` join `team` `t` on(`t`.`TeamID` in (`s`.`TeamA`,`s`.`TeamB`))) join `weekmap` `wm` on(`s`.`Season` = `wm`.`Season` and `s`.`Week` = `wm`.`Week`)) where `s`.`scorea` = `s`.`scoreb` and current_timestamp() > `wm`.`EndDate` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-18 18:08:05
