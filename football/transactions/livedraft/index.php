<?
require_once "utils/start.php";

require "DataObjects/Draftpicks.php";

$draftPicks = new DataObjects_Draftpicks;
$draftPicks->Season = $currentSeason;
$draftPicks->orderBy("Round");
$draftPicks->orderBy("Pick");
$draftPicks->find();

?>

<html>
<head>

<title>WMFFL Live Draft</title>
<link href="draft.css" type="text/css" rel="stylesheet" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="update.jquery.js" type="text/javascript"></script>

</head>

<body id="body_ajax_ld" onresize="resize()" onLoad="$(ready)">

    <div id="statusTable">
       <div id="clockDiv">
        <div id="roundNum" class="clockLine">Round:</div>
        <div id="pickNum" class="clockLine">Pick:</div>
        <div id="clock" class="clockLine">0:00</div>
        <div id="pickTeam" class="clockLine"></div>
        </div>
    </div>

<div id="logInInfo">Logged in as </div><br/>
<div><a class="tools" id="logInLine" onClick="logCheck();">Log In</a></div>

<div id="ajax_ld" class="pagebody">
<div id="left_side">
                <table class="draft_picks_header report" cellspacing="1" align="center">
<!--		<caption><span>Draft Picks</span></caption> -->
                    <tbody>
                        <tr>
                            <th class="round">Rd</th>
                            <th class="pick">Pick</th>
                            <th class="franchise">Franchise</th>
                            <th class="selection">Selection</th>
                        </tr>
                    </tbody>
                </table>

                <div id="draft_picks_container" class="draft_picks_container">
                    <table class="report" cellspacing="1" align="center">
                        <tbody>
<?

while ($draftPicks->fetch()) {
    $roundDist = sprintf("%02d", $draftPicks->Round);
    $pickDist = sprintf("%02d", $draftPicks->Pick);
    $team = $draftPicks->getLink("teamid");
    $player = $draftPicks->getLink("playerid");
    if ($player != null) {
        $playerName = $player->firstname.' '.$player->lastname.' ('.$player->pos.'-'.$player->team.')';
    } else {
        $playerName = "";
    }
    #$teamName = sprintf("%25.25s", $team->Name);
    $teamName = $team->Name;
    if ($draftPicks->Pick % 2 == 0) {
        $row = "eventablerow";
    } else {
        $row = "oddtablerow";
    }
    print <<<EOD
                            <tr id="pick_{$roundDist}_{$pickDist}" class="$row" classname="$row">
                                <td class="round">$roundDist</td>
                                <td class="pick">$pickDist</td>
                                <td class="franchise">$teamName</td>
                                <td class="selection">$playerName</td>
                               <!-- <td class="timestamp"></td>-->
                            </tr>
EOD;
}
?>

                        </tbody>
                    </table>
                </div>
</div>
<!--
            </td>
            <td width="49%" valign="top">
-->
                <div id="right_side">

                <table id="recentStatus" class="draft_picks_header report">
                    <tbody>
                        <tr><th colspan="3">Recent Status</th></tr>
                        <tr><td class="pickLabel">Last Pick</td><td id="lastPickPlayer"></td><td id="lastPickTeam"></td></tr>
                        <tr><td class="pickLabel">On Deck</td><td id="nextPickTeam"></td></tr>
                    </tbody>
                </table>

                <div id="clockTeam_container">
                    <?php include "clockTeam.html"; ?>
                </div>

                <div id="playerForm">
                    <div id="myPick">
                        <div id="myPickLabel">My Pick</div>
                        <div id="choice">No Current Selection</div>
                        <div id="clearButton"><img src="/images/clearButton.png" onclick="buttonClear()"/></div>
                    </div>
                    <form id="pickForm" method="post" action="">
                    <div id="makePick" class="draft_picks_header report">Make A Draft Pick</div>
                    <div id="pickFilter">
                        <div id="filterLabel"><label>Filter:</label></div>
                        <div id="filterSelect">
                            <select name="pos" onChange="showOnly(this);">
                                    <option value="*">All</option>
                                    <option value="QB">QB</option>
                                    <option value="RB">RB</option>
                                    <option value="WR">WR</option>
                                    <option value="TE">TE</option>
                                    <option value="K">K</option>
                                    <option value="OL">OL</option>
                                    <option value="DL">DL</option>
                                    <option value="LB">LB</option>
                                    <option value="DB">DB</option>
                                </select>
                                <select name="nfl" onChange="showOnly(this);">
                                    <option value="*">All</option>
                                    <option value="ARI">ARI</option>
                                    <option value="ATL">ATL</option>
                                    <option value="BAL">BAL</option>
                                    <option value="BUF">BUF</option>
                                    <option value="CAR">CAR</option>
                                    <option value="CHI">CHI</option>
                                    <option value="CIN">CIN</option>
                                    <option value="CLE">CLE</option>
                                    <option value="DAL">DAL</option>
                                    <option value="DEN">DEN</option>
                                    <option value="DET">DET</option>
                                    <option value="GB">GB</option>
                                    <option value="HOU">HOU</option>
                                    <option value="IND">IND</option>
                                    <option value="JAC">JAC</option>
                                    <option value="KC">KC</option>
                                    <option value="LAC">LAC</option>
                                    <option value="LAR">LAR</option>
                                    <option value="MIA">MIA</option>
                                    <option value="MIN">MIN</option>
                                    <option value="NE">NE</option>
                                    <option value="NO">NO</option>
                                    <option value="NYG">NYG</option>
                                    <option value="NYJ">NYJ</option>
                                    <option value="OAK">OAK</option>
                                    <option value="PHI">PHI</option>
                                    <option value="PIT">PIT</option>
                                    <option value="SEA">SEA</option>
                                    <option value="SF">SF</option>
                                    <option value="TB">TB</option>
                                    <option value="TEN">TEN</option>
                                    <option value="WAS">WAS</option>
                                </select>
                                <input type="button" onClick="makePick();" value="Pick"/>
                            </div>
                    </div>
                    <div id="pickPlayer">
                        <div id="selpla">
                            <select name="player" id="mySelect" size="10">
                            </select>
                        </div>
                    </div>
                    </form>
                </div>

                <?php include "logInBlock.html"; ?>

                <div id="farRight">
                <div id="chat_container">
                <div id="chat_table" class="pickLabel">
                    Join us on the <a href="https://hangouts.google.com/hangouts/_/getwellnetwork.com/wmffl-draft" target="_blank">Google Hangout</a>
                </div>
                <!--
                <table id="chat_table">
                <tbody><tr><td>
                    <div id="chat_out">
                    <table class="draft_picks_header report" cellspacing="1" align="center" id="chat">
                        <tbody>
                            <tr>
                                <th class="byName">By</th>
                                <th class="message">Message</th>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </td></tr>
                <tr><td>
                    <form name="chatForm" action="" onSubmit="postMessage(); return false;">
                        <input type="text" id="chat_text_field" maxlength="200" size="30" name="chat"/>
                        <input type="button" value="Post" onClick="postMessage(); return false;"/>
                    </form>
                </td></tr>
                </tbody>
                </table>
                -->
                </div>


                <div id="rosterTeam_container">
                    <?php include "rosterBlock.php"; ?>
                </div>
                </div>
                </div>
</div>

</body>
</html>
