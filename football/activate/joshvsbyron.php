<?
require_once "$DOCUMENT_ROOT/utils/start.php";
include "$DOCUMENT_ROOT/base/scoring.php";

function getOtherTeam($thisTeamID, $thisWeek, $thisSeason, $conn) {
    $getTeamSQL = "SELECT if(s.scorea >= s.scoreb, s.teamA, s.teamB) as 'teamA', ";
    $getTeamSQL .= "if(s.scorea >= s.scoreb, s.teamB, s.teamA) as 'teamB', ";
    $getTeamSQL .= "if(s.scorea >= s.scoreb, tna.name, tnb.name), ";
    $getTeamSQL .= "if(s.scorea >= s.scoreb, tnb.name, tna.name) ";
	$getTeamSQL .= "FROM schedule s, team ta, team tb, teamnames tna, teamnames tnb ";
    $getTeamSQL .= "WHERE s.Week=$thisWeek AND s.Season=$thisSeason ";
    $getTeamSQL .= "AND (s.TeamA=$thisTeamID OR s.TeamB=$thisTeamID) ";
	$getTeamSQL .= "AND s.teamA=ta.teamid and s.teamb=tb.teamid ";
    $getTeamSQL .= "AND ta.teamid=tna.teamid AND tb.teamid=tnb.teamid ";
    $getTeamSQL .= "AND tna.season=s.Season AND tnb.season=s.season ";

    $results = mysql_query($getTeamSQL, $conn);
    $row = mysql_fetch_array($results);
    return $row;
}

function getOtherGames($thisTeamID, $thisWeek, $thisSeason, $conn) {
    $getTeamSQL = "SELECT s.teamA, s.teamB, ta.abbrev as 'aname', tb.abbrev as 'bname', ";
	$getTeamSQL .= "s.scorea, s.scoreb, s.overtime ";
	$getTeamSQL .= "FROM schedule s, teamnames ta, teamnames tb ";
    $getTeamSQL .= "WHERE s.Week=$thisWeek AND s.Season=$thisSeason ";
//    $getTeamSQL .= "AND s.TeamA<>$thisTeamID and s.TeamB<>$thisTeamID ";
	$getTeamSQL .= "AND s.teama=ta.teamid and s.teamb=tb.teamid ";
    $getTeamSQL .= "AND ta.season=s.season AND tb.season=s.season ";
    $results = mysql_query($getTeamSQL, $conn) or die("Database error: ".mysql_error());
	return $results;
//    $row = mysql_fetch_array($results);
//    return $row;
}


function generateReserves($thisTeamID, $currentSeason, $currentWeek) {
    $select = <<<EOD
select p.pos, p.lastname, p.firstname, nr.nflteamid as 'team', n.kickoff, n.secRemain, n.complete, p.flmid, s.*, if (r.dateon is null and p.pos<>'HC', 1, 0) as 'illegal', a.pos as 'startPos', a.teamid as 'teamcheck1', r.teamid as 'teamcheck2', n.secRemain
from newplayers p
JOIN weekmap wm
LEFT JOIN roster r on p.playerid=r.playerid and r.dateon<wm.activationDue and (r.dateoff is null or r.dateoff >= wm.activationDue)
LEFT JOIN revisedactivations a on a.season=wm.season and a.week=wm.week-1 and a.playerid=p.playerid
left join stats s on s.season=wm.season and s.week=wm.week and s.statid=p.flmid
left join nflrosters nr on p.playerid=nr.playerid and nr.dateon <= wm.activationdue and (nr.dateoff is null or nr.dateoff >= wm.activationdue)
left join nflgames n on n.week=wm.week and n.season=wm.season and nr.nflteamid in (n.hometeam, n.roadteam)
WHERE wm.season=$currentSeason and wm.week=$currentWeek and (r.teamid=$thisTeamID or a.teamid=$thisTeamID)
order by p.pos, p.lastname, p.firstname
EOD;
    return $select;
}


function printPlayer($row, $color, $score, $reserveClass="") {
    $printString = "<tr><td class=\"c1 c1$color\"><div class=\"posprefix\">{$row['pos']}</div>";
    $printString .= "<div class=\"PQDO\"> </div>";
    $printString .= "<div class=\"lnx\"><a class=\"player\" onmouseover=\"Q('{$row['flmid']}')\">{$row['lastname']}, {$row['firstname']}</a></div>";
    $printString .= "<div class=\"teamprefix\" align=\"right\">{$row['team']}</div></td>";
    $printString .= "<td class=\"c2 c2$color\">$score</td></tr>";
    return $printString;
}



function playerJavaScript($row, $score) {
    $javascriptLine = "player.M{$row['flmid']} = new ply(\"{$row['flmid']}\", \"$score\", \"{$row['secRemain']}\",";
    $javascriptLine .= "\"{$row['firstname']} {$row['lastname']}\", \"{$row['pos']}\", \"{$row['team']}\", ";
    $scoreString = scoreString($row, $row['pos']);
    $javascriptLine .= "\"$scoreString\");\n";
    return $javascriptLine;
}

?>


<?
#$thisTeamID = $_GET['teamid'] ? $_GET['teamid'] : 2;
//$thisTeamID = isset($teamid) ? $teamid : 2;
$thisTeamID = isset($teamid) ? $teamid : 2;

if ($currentWeek < 1) {
    $thisSeason= isset($season) ? $season : $currentSeason-1;
    $thisWeek = isset($week) ? $week : 16;
} else {
    $thisSeason = isset($season) ? $season : $currentSeason;
    $thisWeek = isset($week) ? $week : $currentWeek;
}

if ($thisSeason == $currentSeason) {
    $weekListSQL = "SELECT week, weekname FROM weekmap WHERE season=$thisSeason AND week<=$currentWeek AND week>=1 ORDER BY week";
} else {
    $weekListSQL = "SELECT week, weekname FROM weekmap WHERE season=$thisSeason AND week<=16 AND week>=1 ORDER BY week";
}
$results = mysql_query($weekListSQL);
$weekList = array();
while ($row = mysql_fetch_array($results)) {
    array_push($weekList, $row);
}

$title = "Current Scores";
$weekLabel = $weekList[$thisWeek-1][1];
?>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<style>
<!--
/*body,font,td,th,p{font-family:Arial,Helvetica,sans-serif;font-size:11px;} */
table.other {border-color: #aabbcc}
.statbox{font-family:Arial,Helvetica,sans-serif;font-size:11px;border:1}
.statblock{border:1px;}
.othertitle {background:#660000 ; color:#e2a500; text-decoration:bold; font-size:14pt; text-align=center; font-family: times, courier;}
.othername {background: #dee3e7; color:#660000; text-decoration:bold; }
.otherscore {background: #dee3e7; color:#660000; text-decoration:bold; }
.boxlink {background: #efefef; font-size: 12px;}
.reserve {font-size: 12px; font-weight: bold; text-align: center}
.showReserve {display: none; width: 100%;background-color:#FFFFFF;border:0px #006699 solid;border-spacing:1px;border-collapse:collapse;width:100%;}
.buffer {height: 10px}
.tablebuffer {height: 25px}
.breakbuff {height: 100px}
.forumline{background-color:#FFFFFF;border:0px #006699 solid;border-spacing:1px;border-collapse:collapse;width:100%;}
.liveTable{border-collapse:collapse;border:0px solid black;width:100%;padding:0px}
.expandStats{border-collapse:collapse;border:1px solid black;padding:0px;}
.scoretitle{text-color:#660000;}

#statline {background-color: #DCDBBE; height: 10px;}

.c1{background-color:#EFEFEF; font-size:11px; margin-right:3px;}
.c2{background-color:#DEE3E7; width: 30px; font-size:11px; text-align:center}
.posprefix{float:left;text-align:right;width:18px}
.teamprefix{width:24px; float: right; text-align:left; padding: 0px 1px 0px 2px;}
.PQDO {
	float: left;
	color: #FF3300;
	font-style: italic;
	width: 20px;
	text-align: center;
	padding: 0px 1px 0px 2px;
}
.player{margin-left:3px;margin-right:3px; padding-left: 2px; float:left;}
.player:hover{color:#00B0B0;}
.lnx{font-weight:700;text-decoration:none;min-width:50px}
.bye {font-color: red}
.final {font-color: black}
.later {font-color: green}
.current {font-color: blue}

.R {text-align: right;}
.L {text-align: left;}
.C {text-align: center;}
.top {align: top;}

.c1pts {font-weight:700; margin-left:3px; padding-right: 3px; text-align: right;}
.c2pts {font-weight:700}

.c1bye {background-color:#F49F9F;}
.c2bye {background-color:#EE7173;}
.c1illegal {background-color:#F4F49F;}
.c2illegal {background-color:#EEF173;}
.c1current {background-color:#9F9FF4;}
.c2current {background-color:#6F71F3;}
.c1later {background-color:#9FF49F;}
.c2later {background-color:#6FF173;}
-->
</style>


<script language="javascript">
<!--- 
// --->
</script>

<HR size = "1">
<table border="0" width="100%">

<tr><td valign="top">

<div class="statbox">
<table class="liveTable">
<tr><td colspan="6" align="center" class="othertitle"><? print $weekLabel; ?> Scores</td></tr>
<tr><td class="buffer">&nbsp;</td></tr>
<tr><td>
<?
$teams = getOtherTeam($thisTeamID, $thisWeek, $thisSeason, $conn);
$teams = array(2, 3, 'Werewolves', 'Norsemen');
$thisWeek = 15;

$javascriptString = "";
for ($i = 0; $i<2; $i++) {
?>
<?
    $select = generateReserves($teams[$i], $thisSeason, $thisWeek);
	$printString[$i] = "";
	$reserveString[$i] = "";

    $results = mysql_query($select, $conn) or die(mysql_error());

    $totalPoints[$i] = 0;
    $offPoints[$i] = 0;
    $defPoints[$i] = 0;
    $penalty[$i] = 0;
    while ($row = mysql_fetch_array($results)) {
        if ($row["teamcheck1"] != null && $row["teamcheck1"]!=$teams[$i]) {
            continue;
        }

        $pts = 0;
        switch ($row['pos']) {
            case 'HC' :
				$pts = scoreHC($row);
	 			break;
            case 'QB' :
				$pts = scoreQB($row);
				break;
            case 'RB' :
            case 'WR' :
				 $pts = scoreOffense($row);
				 break;
			case 'TE' :    
				 $pts = scoreTE($row);
				 break;
			case 'K' :
				$pts = scoreK($row);
				break;
			case 'OL' :
				$pts = scoreOL($row);
				 break;
            case 'DL' :
            case 'LB' :
            case 'DB' :
				 $pts = scoreDefense($row);
				 break;
        }
        if ($row['startPos'] != null) {
            $includePts = true;
        } else {
            $includePts = false;
        }

        if ($includePts) {
            if ($row['pos'] == 'DL' || $row['pos']=='LB' || $row['pos']=='DB') {
                $defPoints[$i] += $pts;
            } else {
                $offPoints[$i] += $pts;
            }
        }

        $gameTime = strtotime($row['kickoff']);
        $now = time();

        if ($includePts) {
            if ($row['illegal']==1 || ($row['teamcheck2']!=$teams[$i] && $row["pos"]!="HC")) {
                $printString[$i] .= printPlayer($row, "illegal", -2);
                if ($includePts) {
                    $penalty[$i] += 2;
                    if ($row["pos"]=='DL' || $row["pos"]=='LB' || $row["pos"]=="DB") {
                        $defPoints[$i] -= $pts;
                    } else {$offPoints[$i] -= $pts;}
                    $totalPoints[$i] -= $pts;
                }
            } elseif ($row['kickoff']==null && $row['pos']!='HC') {
                $printString[$i] .= printPlayer($row, "bye", -2);
                if ($includePts) {
                    $penalty[$i] += 2;
                    if ($row["pos"]=='DL' || $row["pos"]=='LB' || $row["pos"]=="DB") {
                        $defPoints[$i] -= $pts;
                    } else {$offPoints[$i] -= $pts;}
                    $totalPoints[$i] -= $pts;
                }
            } elseif ($row['complete']==1) {
                $printString[$i] .= printPlayer($row, "final", $pts);
            } elseif ($now>$gameTime && $row['kickoff']!=null) {
                $printString[$i] .= printPlayer($row, "current", $pts);
            } elseif ($now<=$gameTime) {
                $printString[$i] .= printPlayer($row, "later", $pts);
            } else {
                $printString[$i] .= printPlayer($row, "final", 0);
            }

            $totalPoints[$i] += $pts;
        } else {
            if ($row['kickoff']==null) {
                $reserveString[$i] .= printPlayer($row, "final", "-", "showReserve");
            } elseif ($row['complete']==1) {
                $reserveString[$i] .= printPlayer($row, "final", $pts, "showReserve");
            } else if ($now>$gameTime && $row['kickoff']!=null) {
                $reserveString[$i] .= printPlayer($row, "current", $pts, "showReserve");
            } elseif ($now<=$gameTime) {
                $reserveString[$i] .= printPlayer($row, "later", $pts, "showReserve");
            } else {
                $reserveString[$i] .= printPlayer($row, "final", 0, "showReserve");
            }
        }
        
        $javascriptString .= playerJavaScript($row, $pts);
    }

    $printString[$i] .= "<tr><td class=\"c1buffer\"></td></tr>";
    $printString[$i] .= "<tr><td class=\"c1 c1pts\">Offensive Points</td><td class=\"c2 c2pts\">{$offPoints[$i]}</tr>";
    $printString[$i] .= "<tr><td class=\"c1 c1pts\">Defensive Points</td><td class=\"c2 c2pts\">{$defPoints[$i]}</tr>";
    if ($penalty[$i] > 0) {
        $printString[$i] .= "<tr><td class=\"c1 c1pts\">Penalties</td><td class=\"c2 c2pts\">-{$penalty[$i]}</tr>";
    }
}

$startPoints[0] = $offPoints[0] - $defPoints[1] - $penalty[0];
$startPoints[1] = $offPoints[1] - $defPoints[0] - $penalty[1];

$printString[0] = "<TR><TH>".$teams[2]."</TH><th>".$startPoints[0]."</th></TR>".$printString[0]."<tr><td></td></tr><tr><td class=\"reserve\"><a onClick=\"showReserves('sr0');\" class=\"reserve\">Reserves (+)</a></td></tr><tr><td colspan=\"2\"><table class=\"showReserve\" id=\"sr0\">".$reserveString[0]."</table></td></tr>";
$printString[1] = "<TR><TH>".$teams[3]."</TH><th>".$startPoints[1]."</th></TR>".$printString[1]."<tr><td></td></tr><tr><td class=\"reserve\"><a onClick=\"showReserves('sr1');\" class=\"reserve\">Reserves (+)</a></td></tr><tr><td colspan=\"2\"><table class=\"showReserve\" id=\"sr1\">".$reserveString[1]."</table></td></tr>";


?>

<td width="5%" valign="top">
</td><td width="40%" valign="top">
    <table class="forumline"><? print $printString[0]; ?></table>
</td> <td width="25px" valign="top">
</td> <td width="40%" valign="top">
    <table class="forumline"><? print $printString[1]; ?></table>
</td> <td width="5%" valign="top">
</td></tr>
</table>
</div>

<div class="tablebuffer">&nbsp;</div>

<table class="expandStats" align="center">

<tbody><tr>
				<td class="cat C" colspan="6">Individual Player Summary</td>
			</tr>
			<tr>
				<th class="C" id="mugname" colspan="6">&nbsp;</th>
			</tr>
			<tr>
                <td class="row c1 R" style="width: 16%;">Pos:</td>
                <td class="row c1 L" style="width: 16%;" id="pos">&nbsp;</td>
				<td class="row c1 R" style="width: 16%;">NFL:</td>
				<td class="row c1 L" style="width: 16%;" id="prmatchup">&nbsp;</td>
				<td class="row c1 R" style="width: 16%;">Current Score:</td>
				<td class="row c1 scoreActive" id="prscore" style="width: 16%;">&nbsp;</td>
			</tr>
			<tr>
                <!--
				<td class="row c1 R">Injury class: </td>
                <td style="color: rgb(102, 153, 153);" class="row c1 L healthy" id="prhealth">Healthy</td>
                -->
				<td class="row c1 R"></td>
                <td style="color: rgb(102, 153, 153);" class="row c1 L healthy" id="prhealth"></td>
				<td class="row c1 R">Time left: </td>
				<td class="row c1 L scoreActive" id="prminleft"> </td>
				<td class="row c1">&nbsp;</td>
				<td class="row c1">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="6" id="statline"></td>

			</tr>
			<tr>
				<td class="row c1 R top" colspan="5" valign="top" id="breakdown">&nbsp;</td>
				<td class="row c1 breakbuff">&nbsp;</td>
			</tr>
			</tbody>


</table>



<script language="javascript">
<!--- 

function showReserves(elementId) {
    var element = document.getElementById(elementId);
    var display = element.style.display;
    if (display == "table") {
        if (element.style.setAttribute) {
            element.style.setAttribute('display', 'none');
        } else {
            element.style.display = 'none';
        }
    } else {
        if (element.style.setAttribute) {
            element.style.setAttribute('display', 'table');
        } else {
            element.style.display = 'table';
        }
    }
}

function pad (num, length) {
    var str = '' + num;
    while (str.length < length) {
        str = '0' + str;
    }
    return str;
}


var clRed = '#CC3333';

function ply (identifier, score, timeRem, name, position, nflteam, details)
{
	this.identifier		= identifier;
	this.score		    = score;
	this.name		    = name;
	this.position		= position;
	this.nflteam		= nflteam;
	this.details		= details;
	this.timeRem		= timeRem;
}


var player = new Object();
<?
    print $javascriptString;
?>

var mugname   = document.getElementById("mugname").childNodes[0];
var plpos  = document.getElementById("pos").childNodes[0];
var prmatchup = document.getElementById("prmatchup").childNodes[0];
var prscore   = document.getElementById("prscore").childNodes[0];
var prminleft = document.getElementById("prminleft").childNodes[0];
var ping      = document.getElementById("ping");
var statline  = document.getElementById("statline");

function update_breakdown (tagID, detailString)
{
	var breaknode = document.getElementById(tagID);
	var n;
	var nindex;
	var numKids = 0;
	if (breaknode.hasChildNodes())
	{
		numKids = breaknode.childNodes.length;
		var numGrandKids, k, y;
		for (k = numKids - 1; k >= 0; k--)
		{
			if (breaknode.childNodes[k].hasChildNodes())
			{
				numGrandKids = breaknode.childNodes[k].childNodes.length;
				for (y = numGrandKids - 1; y >= 0; y--)
				{
					breaknode.childNodes[k].removeChild(breaknode.childNodes[k].childNodes[y]);
				}
			}
		}
	}
	var descriptNode;
	var subscoreNode;

	var encoded_details = detailString.split("^");
	var numDetails = (encoded_details.length > 1) ? (encoded_details.length / 2) : 0;
	for (n = 0; n < numDetails; n++)
	{
		nindex = n * 2;

		descriptNode = document.createElement('div');
		subscoreNode = document.createElement('div');

		descriptNode.appendChild(document.createTextNode(encoded_details[nindex]));
		subscoreNode.appendChild(document.createTextNode(encoded_details[nindex+1]));


		subscoreNode.style.color       = clRed;
		subscoreNode.style.fontWeight  = '700';
		subscoreNode.style.width       = '40px';
		subscoreNode.style.paddingLeft = '5px';
		subscoreNode.style.textAlign   = 'right';
		subscoreNode.style.cssFloat  = 'right';
		subscoreNode.style.styleFloat  = 'right';

		if (nindex >= numKids)
		{
			breaknode.appendChild(subscoreNode);
		}
		else
		{
			breaknode.replaceChild(subscoreNode, breaknode.childNodes[nindex]);
		}
		if (nindex + 1 >= numKids)
		{
			breaknode.appendChild(descriptNode);
		}
		else
		{
			breaknode.replaceChild(descriptNode, breaknode.childNodes[nindex+1]);
		}
	}
}

function Q (index)
{
    /*
	if (index.substr(index.length - 2, 2) == 'P0')
	{
		return;
	}
    */
	var Pindex = 'M' + index;
	//mugname.nodeValue = player[Pindex].position + ' ' + player[Pindex].name;
    mugname.nodeValue = player[Pindex].name;
    prmatchup.nodeValue = player[Pindex].nflteam;
    plpos.nodeValue = player[Pindex].position;
    prscore.nodeValue = player[Pindex].score;

    var min = Math.floor(player[Pindex].timeRem / 60);
    if (min > 60) {min = min - 60; }
    var sec = player[Pindex].timeRem % 60;
    prminleft.nodeValue = min + ":" + pad(sec,2);
    if (min < 0 || sec < 0) {
        prminleft.nodeValue = "Overtime";
    }

    //prminleft.nodeValue = player[Pindex].timeRem;
	//set_injury (player[Pindex].injstatus, prhealth);
	//prmatchup.nodeValue = player[Pindex].matchup;
	//statline.childNodes[0].nodeValue = player[Pindex].summary;
	//statline.style.backgroundColor = (player[Pindex].finalstats == 2) ? clGold : clSilver;
	//copy_score (Pindex, prscore);
	//copy_timeleft (Pindex, prminleft);
	update_breakdown ('breakdown', player[Pindex].details);

	//Qid_matrix[last_gid] = index;
}

// --->
</script>


</td><td width="150" valign="top">

<table width="100%" border="0" class="other">

<tr><td class="othertitle" colspan="3" align="center">WMFFL Scores</td></tr>

<?
$gameresults = getOtherGames($thisTeamID, $thisWeek, $thisSeason, $conn);
$count = 0;
while ($row = mysql_fetch_array($gameresults)) {
    $count++;
    if ($row["scorea"] >= $row["scoreb"]) {
        $winningName = $row["aname"];
        $winningScore = $row["scorea"];
        $linkId = $row["teamA"];
        $losingName = $row["bname"];
        $losingScore = $row["scoreb"];
    } else {
        $winningName = $row["bname"];
        $winningScore = $row["scoreb"];
        $linkId = $row["teamA"];
        $losingName = $row["aname"];
        $losingScore = $row["scorea"];
    }
        if ($winningScore == "") {
            $winningScore = 0;
        }
        if ($losingScore == "") {
            $losingScore = 0;
        }
?>
    <tr><td class="buffer" colspan="3"></td></tr>
    <div id="AA<? print $count; ?>">
    <tr><td class="othername" align="left"><div class="othername"><? print $winningName; ?></div></td>
    <td class="otherscore" align="center"><div class="otherscore"><? print $winningScore; ?></div></td>
    <td class="boxlink" align="center" rowspan="2">
        <? print "<a href=\"?teamid=$linkId&week=$thisWeek&season=$thisSeason\" class=\"boxlink\">"; ?>
        Box Score</a>
        <? if ($row["overtime"] > 0) { print "<div class=\"otherscore\">OT</div>"; } ?>
    </td></tr>
    <tr><td class="othername" align="left"><div class="othername"><? print $losingName; ?></div></td>
    <td class="otherscore" align="center"><div class="otherscore"><? print $losingScore; ?></div></td>
    </td></tr>
    </div>
<?
}
?>

</table>

<center><p><b>Previous Weeks</b>
<form action="currentscore.php" method="post">
<input type="hidden" name="season" value="<? print $thisSeason; ?>"/>
<input type="hidden" name="teamid" value="<? print $thisTeamID; ?>"/>
<select name="week" onchange="submit();">
    <option value="" selected>Select Week</option>
<?
foreach ($weekList as $row) {
    $weekID = $row["week"];
    $thisWeekName = $row["weekname"];
    print "<option value=\"$weekID\">$thisWeekName</option>";
}
?>
</select>
</form>
</center>

</td></tr>
</table>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
