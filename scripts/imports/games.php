<?php
include dirname(__FILE__)."/../base.php";

//$week = $_REQUEST['week'];
//$week = 4;
$sql = "select week, season from weekmap where startDate < now() and endDate > now()";
$results = mysqli_query($conn, $sql) or die ("Unable to get this week: " . mysqli_error($conn));
list($curWeek, $season) = mysqli_fetch_array($results);
$curWeek = 0;

for ($week = $curWeek-1; $week <= 17; $week++) { 
    loadWeekGames($season, $week);
}



function determineTeam($teamId) {
    switch ($teamId) {
        case 'BUF':  case 'IND':  case 'MIA':  case 'NYJ':  case 'CIN':
        case 'CLE':  case 'TEN':  case 'JAC':  case 'PIT':  case 'DEN':
        case 'OAK':  case 'SEA':  case 'DAL':  case 'NYG':  case 'PHI':
        case 'ARI':  case 'WAS':  case 'CHI':  case 'DET':  case 'MIN':
        case 'ATL':  case 'CAR':  case 'BAL':  case 'HOU':  case 'LAR':
        case 'LAC':
            $teamVal = $teamId;
            break;
        case 'NEP': $teamVal = 'NE'; break;
        case 'KCC': $teamVal = 'KC'; break;
        case 'SDC': $teamVal = 'SD'; break;
        case 'GBP': $teamVal = 'GB'; break;
        case 'TBB': $teamVal = 'TB'; break;
        case 'NOS': $teamVal = 'NO'; break;
        case 'SFO': $teamVal = 'SF'; break;
	case 'RAM': $teamVal = 'LAR'; break;
        default:    $teamVal = '';
    }
    return $teamVal;
}


function loadWeekGames($season, $week) {
    global $conn;
    $request_url = "http://www.myfantasyleague.com/$season/export?TYPE=nflSchedule&W=$week";
    $xml = simplexml_load_file($request_url) or die("Feed not loading");

    $matchupList = array();
    print "<pre>";

    $block = array();
    $first = true;
    foreach ($xml->matchup as $game) {
        #print_r($game);

        $kickoff = $game['kickoff'];
        $secRemain = $game['gameSecondsRemaining'];
        #print "$kickoff - $secRemain<br/>";
        $team = $game->team;
        foreach ($game->team as $team) {
            #print_r($team);
            $teamName = $team['id'];
            $home = $team['isHome'];
            $score = $team['score'];
            #print "&nbsp;&nbsp;&nbsp;&nbsp;$teamName - $home - $score<br/>";
            if ($home == 1) {
                $homeTeam = $teamName;
                $homeScore = $score;
            } else {
                $roadTeam = $teamName;
                $roadScore = $score;
            }
        }
        if ($secRemain == 0) {
            $complete = 1;
        } else if ($secRemain == 3600) {
            $complete = 0;
            $homeScore = "NULL";
            $roadScore = "NULL";
        } else {
            $complete = 1;
        }

        if (!$first) {
            $string = ", ";
        } else {
            $first = false;
            $string = "";
        }
        $homeTeam = determineTeam($homeTeam);
        $roadTeam = determineTeam($roadTeam);
        $string .= "($season, $week, '$homeTeam', '$roadTeam', FROM_UNIXTIME($kickoff), $secRemain, $complete, $homeScore, $roadScore)";

        array_push($block, $string);
    }

    print "</pre>";

    print "REPLACE INTO nflgames VALUES ";
    $string = "REPLACE INTO nflgames VALUES ";
    foreach($block as $game) {
        print $game;
        $string .= $game;
    }
    print "<br/>";

    mysqli_query($conn, $string) or die("Dead: " . mysqli_error($conn));
}

?>
