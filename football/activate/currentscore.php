<?
require_once "utils/connect.php";
include "base/scoring.php";
include "scoreFunctions.php";

// Get Team to show
$thisTeamID = isset($teamid) ? $teamid : 2;

// Determine season and week to show
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
$results = mysqli_query($conn, $weekListSQL);
$weekList = array();
while ($row = mysqli_fetch_array($results)) {
    array_push($weekList, $row);
}

$weekLabel = $weekList[$thisWeek - 1][1];
$title = "Current Scores";
$cssList = array("score.css");
include "base/menu.php";
?>


<table border="0" width="100%">

<tr><td valign="top">

<div class="statbox">
<table class="liveTable">
<tr><td colspan="6" align="center" class="othertitle"><? print $weekLabel; ?> Scores</td></tr>
<tr><td class="buffer">&nbsp;</td></tr>
<tr><td>
<?
$teams = getOtherTeam($thisTeamID, $thisWeek, $thisSeason, $conn);

$javascriptString = "";
for ($i = 0; $i<2; $i++) {
?>
<?
    $select = generateReserves($teams[$i], $thisSeason, $thisWeek);
	$printString[$i] = "";
	$reserveString[$i] = "";
    $timeRemain = 0;

    $results = mysqli_query($conn, $select) or die(mysqli_error($conn));

    $totalPoints[$i] = 0;
    $offPoints[$i] = 0;
    $defPoints[$i] = 0;
    $penalty[$i] = 0;
    while ($row = mysqli_fetch_array($results)) {
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
            $timeRemain += $row['secRemain'];
        } else {
            $includePts = false;
        }

        $gameTime = strtotime($row['kickoff']);
        $now = time();
        if ($teamnum == 2) {
#            print_r($row);
#            print "{$row['kickoff']} = $gameTime<br/>";
#            print "$gameTime - $now<br/>";
        }
        if ($now > strtotime($row['ActivationDue'])) {
            if ($row["GPMe"] == "Me" && $row["GPThem"] != "Them") {
                $pts = 2 * $pts;
            } else if ($row["GPThem"] == "Them" && $row["GPMe"] != "Me") {
                if ($pts > 0) {
                    $pts = ceil($pts/2.0);
                }
            }
        }

        if ($includePts) {
            if ($row['pos'] == 'DL' || $row['pos']=='LB' || $row['pos']=='DB') {
                $defPoints[$i] += $pts;
            } else {
                $offPoints[$i] += $pts;
            }
        }
#print_r($row);

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
    $hrRemain = floor($timeRemain / 60);
    $secRemain = $timeRemain % 60;
    $printTime = $hrRemain . ":" . str_pad($secRemain, 2, "0", STR_PAD_LEFT);
    $printString[$i] .= "<tr><td class=\"c1 c1pts\">Time Remaining</td><td class=\"c2 c2pts\">$printTime</tr>";
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

<table class="expandStats" align="center" border="0">

<tbody><tr>
    <td class="cat C" colspan="2">Individual Player Summary</td>
			</tr>
			<tr>
                <th class="C" id="mugname" colspan="2">&nbsp;</th>
			</tr>
			<tr>
                <td class="c1 L pl-1">Pos: <span id="pos">&nbsp;</span></td>
                <td class="c1 L pr-1">NFL: <span class="scoreActive" id="prmatchup">&nbsp;</span></td>
			</tr>
			<tr>
                <!--
				<td class="row c1 R">Injury class: </td>
                <td style="color: rgb(102, 153, 153);" class="row c1 L healthy" id="prhealth">Healthy</td>
                -->
                <td class="c1 L pl-1">Time left: <span id="prminleft">&nbsp;</span></td>
                <td class="c1 L pr-1">Current Score: <span id="prscore">&nbsp;</span></td>
			</tr>
			<tr>
                <td colspan="2" id="statline"></td>

			</tr>
			<tr>
                <td class="c1 R top breakbuff pr-2" colspan="2" valign="top" id="breakdown">&nbsp;</td>
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

        subscoreNode.className = 'statItem';
        /*
		subscoreNode.style.color       = clRed;
		subscoreNode.style.fontWeight  = '700';
		subscoreNode.style.width       = '40px';
		subscoreNode.style.paddingLeft = '5px';
		subscoreNode.style.textAlign   = 'right';
		subscoreNode.style.cssFloat  = 'right';
		subscoreNode.style.styleFloat  = 'right';
        */
        descriptNode.style.textAlign = 'right';
        descriptNode.style.styleFloat = 'right';
       // descriptNode.style.cssFloat    = 'left';
       // descriptNode.style.styleFloat    = 'left';

    //    fullNode = document.createElement('div');
    //    fullNode.appendChild(descriptNode);
    //    fullNode.appendChild(subscoreNode);
    //    breakNode.appendChild(fullNode);


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
while ($row = mysqli_fetch_array($gameresults)) {
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

        <div class="mt-2 justify-content-center text-center"><p><b>Previous Weeks</b>
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
        </div>

</td></tr>
</table>

<? include "base/footer.html"; ?>
