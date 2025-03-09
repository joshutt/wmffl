<?php

// football/teams/history/dataRetrieval.php

/**
 * @param $viewteam
 * @param $conn
 * @return array
 */
function getPlayoffRecord($viewteam, $conn): array
{
    $recordQuery = <<<EOD
    SELECT if (playoffs=0, 'Toilet Bowl', 'Playoffs') as `event`,
    sum(if(s.teama=$viewteam, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb > s.scorea, 1, 0))) as `win`,
    sum(if(s.teama=$viewteam, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb < s.scorea, 1, 0))) as `lose`,
    sum(if(s.scorea=s.scoreb, 1, 0)) as `tie`
    FROM schedule s
    WHERE $viewteam in (s.teama, s.teamb) and postseason=1
    group by playoffs desc
    EOD;

    $result = mysqli_query($conn, $recordQuery);
    $seasonArray = [];

    while ($recordList = mysqli_fetch_array($result)) {
        $newArray = array($recordList['event'], $recordList['win'], $recordList['lose'], $recordList['tie']);
        $pct = calculateWinPercentage($recordList['win'], $recordList['lose'], $recordList['tie']);
        $newArray[] = $pct;
        $seasonArray[] = $newArray;
    }
    return $seasonArray;
}

/**
 * @param $viewteam
 * @param $currentWeek
 * @param $currentSeason
 * @param $conn
 * @return array
 */
function getRegularSeasonRecords($viewteam, $currentWeek, $currentSeason, $conn): array
{
    $recordQuery = <<<EOD
    SELECT s.season,
    sum(if(s.teama=$viewteam, if(s.scorea>s.scoreb, 1, 0), if(s.scoreb > s.scorea, 1, 0))) as `win`,
    sum(if(s.teama=$viewteam, if(s.scorea<s.scoreb, 1, 0), if(s.scoreb < s.scorea, 1, 0))) as `lose`,
    sum(if(s.scorea=s.scoreb, 1, 0)) as `tie`
    FROM schedule s
    WHERE $viewteam in (s.teama, s.teamb) and postseason=0
    and if($currentWeek = 0, s.season<>$currentSeason, true)
    GROUP BY s.season
    order by s.season desc
    EOD;

    $result = mysqli_query($conn, $recordQuery);
    $seasonArray = [];

    while ($recordList = mysqli_fetch_array($result)) {
        $newArray = array($recordList['season'], $recordList['win'], $recordList['lose'], $recordList['tie']);
        if ($newArray[1] + $newArray[2] + $newArray[3] != 0) {
            $pct = calculateWinPercentage($recordList['win'], $recordList['lose'], $recordList['tie']);
            $newArray[] = $pct;
            $seasonArray[] = $newArray;
        }
    }
    return $seasonArray;
}

/**
 * @param $seasonArray
 * @return array
 */
function getAllTimeRecord($seasonArray): array
{
    $wins = 0;
    $lose = 0;
    $tie = 0;
    foreach ($seasonArray as $innerArray) {
        $wins += $innerArray[1];
        $lose += $innerArray[2];
        $tie += $innerArray[3];
    }

    $newArray = array('All-Time', $wins, $lose, $tie);
    $pct = calculateWinPercentage($wins, $lose, $tie);
    $newArray[] = $pct;
    return $newArray;
}

/**
 * @param $viewteam
 * @param $conn
 * @return array
 */
function getPlayoffResults($viewteam, $conn): array
{
    $playoffQuery = <<<EOD
    SELECT if(s.playoffs=0, 'Toilet Bowl', if(s.championship=0, 'Playoffs', 'Championship')) as `event`,
    s.season, n.name as 'otherTeam',
    if (s.TeamA=$viewteam, s.scorea, s.scoreb) as 'myscore',
    if (s.TeamA=$viewteam, s.scoreb, s.scorea) as 'otherscore'
    FROM schedule s, teamnames n
    WHERE $viewteam in (s.TeamA, s.TeamB) and s.postseason=1
    and n.season=s.season and n.teamid<>$viewteam and n.teamid in (s.TeamA, s.TeamB)
    order by s.season asc, s.week asc
    EOD;

    $result = mysqli_query($conn, $playoffQuery) or die('Mysql error: ' . mysqli_error($conn));
    $playoffResults = [];

    while ($recordList = mysqli_fetch_array($result)) {
        $singleGame = [];

        if ($recordList['myscore'] > $recordList['otherscore']) {
            $label = 'Beat';
        } else {
            $label = 'Lost to';
        }
        $singleGame[0] = $recordList['event'] . ' ' . $recordList['season'];
        $singleGame[1] = $label . ' ' . $recordList['otherTeam'];
        $singleGame[2] = $recordList['myscore'] . '-' . $recordList['otherscore'];
        $playoffResults[] = $singleGame;
    }
    return $playoffResults;
}

/**
 * @param $viewteam
 * @param $conn
 * @return array
 */
function getTitles($viewteam, $conn): array
{
    $titleQuery = <<<EOD
    select t.season, t.type, d.name as 'divName'
    from titles t, teamnames n, division d
    where t.teamid=$viewteam and t.teamid=n.teamid and t.season=n.season
    and n.divisionid=d.divisionid and t.season between d.startYear and d.endYear
    order by t.season asc
    EOD;

    $result = mysqli_query($conn, $titleQuery) or die('Mysql error: ' . mysqli_error($conn));
    $leagueTitles = [];
    $divisionTitles = [];

    while ($titles = mysqli_fetch_array($result)) {
        if ($titles['type'] == 'League') {
            $leagueTitles[] = $titles['season'];
        } else if ($titles['type'] == 'Division') {
            $pair = array($titles['season'], $titles['divName']);
            $divisionTitles[] = $pair;
        }
    }
    return array($leagueTitles, $divisionTitles);
}

/**
 * @param $viewteam
 * @param $conn
 * @return array
 */
function getPastNames($viewteam, $conn): array
{
    $namedArray = [];
    $nameQuery = "select season, name from teamnames where teamid=$viewteam order by season asc";
    $result = mysqli_query($conn, $nameQuery);
    $prevName = '';
    $startSeason = 0;
    while ($nameSet = mysqli_fetch_array($result)) {
        if ($nameSet['name'] != $prevName) {
            if ($startSeason != 0) {
                $oneName = array('start' => $startSeason, 'end' => $nameSet['season'] - 1, 'name' => $prevName);
                $namedArray[] = $oneName;
            }
            $startSeason = $nameSet['season'];
            $prevName = $nameSet['name'];
        }
    }
    $oneName = array('start' => $startSeason, 'end' => 0, 'name' => $prevName);
    $namedArray[] = $oneName;
    return $namedArray;
}

/**
 * @param $viewteam
 * @param $conn
 * @return array
 */
function getPastOwners($viewteam, $conn): array
{
    $ownerArray = [];
    $ownerQuery = "SELECT u.name, o.season, o.primary from owners o, user u where o.userid=u.userid and o.teamid=$viewteam order by o.season asc, o.primary asc";
    $result = mysqli_query($conn, $ownerQuery) or die('Die: ' . mysqli_error($conn));
    $prevName = '';
    $finalName = '';
    $startSeason = 0;
    while ($ownerSet = mysqli_fetch_array($result)) {
        if ($ownerSet['primary'] == 0) {
            $finalName .= ' and ' . $ownerSet['name'];
            continue;
        }
        $newFinalName = $ownerSet['name'] . $finalName;
        $finalName = $newFinalName;

        if ($finalName != $prevName) {
            if ($startSeason != 0) {
                $oneName = array('start' => $startSeason, 'end' => $ownerSet['season'] - 1, 'name' => $prevName);
                $ownerArray[] = $oneName;
            }
            $startSeason = $ownerSet['season'];
            $prevName = $finalName;
        }
        $finalName = '';
    }
    $oneName = array('start' => $startSeason, 'end' => 0, 'name' => $prevName);
    $ownerArray[] = $oneName;
    return $ownerArray;
}