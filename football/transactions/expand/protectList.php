<?
require_once "utils/start.php";

$protect = array();
$pullback = array();
$alternate = array();

if ($isin) {

$sql = <<<EOD
    select playerid, type from expansionprotections where teamid=$teamnum order by type
EOD;

$results = mysql_query($sql) or die("Unable to get expansion protections: ".mysql_error());

while (list($playerid, $type) = mysql_fetch_array($results)) {
    if ($type == "protect") {
        array_push($protect, $playerid);
    } else if ($type == "pullback") {
        array_push($pullback, $playerid);
    } else if ($type == "alternate") {
        array_push($alternate, $playerid);
    }
}
}


$title = "Expansion Protection";
?>

<style>
#roster {
    color: ddd;
    border: 1px;
    float: left;
    width: 50%;
}
#protectBlock {
    float: right;
    border: 1px;
    width: 50%;
}

#protect {
    padding: 5px;
}

#pullback {
    padding: 5px;
}

#alternate {
    padding: 5px;
}

</style>

<script language="javascript">

protect = new Array();
var pullback = new Array();
alternate = new Array();

function rotate(playerRow) {
//    alert(playerRow.childNodes[0].innerHTML);
//    document.getElementById("p1").innerHTML = playerRow.childNodes[0].innerHTML;
}


function loadPlayers() {
<?
    foreach ($protect as $playerid) {
        print "protect.push($playerid);";
    }
    foreach ($pullback as $playerid) {
        print "pullback.push($playerid);";
    }
    foreach ($alternate as $playerid) {
        print "alternate.push($playerid);";
    }
?>

    for (x in protect) {
        proElement = document.getElementById("pro"+protect[x]);
        proElement.checked = true;
    }
    for (x in pullback) {
        proElement = document.getElementById("pb"+pullback[x]);
        proElement.checked = true;
    }
    for (x in alternate) {
        proElement = document.getElementById("alt"+alternate[x]);
        proElement.checked = true;
    }

    listFill();
}


function saveClick(player) {
    if (player.checked) {
        //alert(player.value +"-"+player.name);
        //alert(player)

        if (player.name == 'pro[]') {
            if (!contains(protect, player.value)) {
                if (protect.length >= 5) {
                    alert ("You May only protect 5 players");
                    player.checked = false;
                } else {
                    protect.push(player.value);

                    pbElement = document.getElementById("pb"+player.value);
                    altElement = document.getElementById("alt"+player.value);

                    if (pbElement.checked || altElement.checked) {
                        pbElement.checked = false;
                        pullback = remove(pullback, player.value);
                        altElement.checked = false;
                        alternate = remove(alternate, player.value);
                    }
                }
            }


        } else if (player.name == 'pb[]') {
            if (!contains(pullback, player.value)) {
                if (pullback.length >= 2) {
                    alert ("You May only pull-back 2 players");
                    player.checked = false;
                } else {
                    pullback.push(player.value);

                    proElement = document.getElementById("pro"+player.value);
                    altElement = document.getElementById("alt"+player.value);

                    if (proElement.checked || altElement.checked) {
                        proElement.checked = false;
                        protect = remove(protect, player.value);
                        altElement.checked = false;
                        alternate = remove(alternate, player.value);
                    }
                }
            }
        } else if (player.name == 'alt[]') {
            if (!contains(alternate, player.value)) {
                if (alternate.length >= 1) {
                    alert ("You May only choose 1 alternate");
                    player.checked = false;
                } else {
                    alternate.push(player.value);

                    pbElement = document.getElementById("pb"+player.value);
                    proElement = document.getElementById("pro"+player.value);

                    if (pbElement.checked || proElement.checked) {
                        pbElement.checked = false;
                        pullback = remove(pullback, player.value);
                        proElement.checked = false;
                        protect = remove(protect, player.value);
                    }
                }
            }
        }

    } else {
        if (player.name == "pro[]") {
            protect = remove(protect, player.value);
        } else if (player.name == "pb[]") {
            pullback = remove(pullback, player.value);
        } else if (player.name == "alt[]") {
            alternate = remove(alternate, player.value);
        }
    }

    listFill();
}


function checkform() {
    if (protect.length == 5 && pullback.length == 2 && alternate.length == 1) {
        return true;
    }
    return confirm("You haven't protected your full allocation.  Are you sure you want to submit these protections?");
}

function listFill() {
    id = -1;
    for (id in protect) {
        //alert(protect[id]);
        name = document.getElementById(protect[id]).cells[3].innerHTML;
        i = new Number(id)+1;
        key = "p"+i;
        document.getElementById(key).innerHTML = i + "."+name;
    }
    id++;
    while (id < 5) {
        i = new Number(id)+1;
        key = "p"+i;
        document.getElementById("p"+i).innerHTML = i + ".";
        id++;
    }

    id = -1;
    for (id in pullback) {
        //alert(protect[id]);
        name = document.getElementById(pullback[id]).cells[3].innerHTML;
        i = new Number(id)+1;
        key = "pb"+i;
        document.getElementById(key).innerHTML = i + "."+name;
    }
    id++;
    while (id < 2) {
        i = new Number(id)+1;
        key = "pb"+i;
        document.getElementById("pb"+i).innerHTML = i + ".";
        id++;
    }


    id = -1;
    for (id in alternate) {
        //alert(protect[id]);
        name = document.getElementById(alternate[id]).cells[3].innerHTML;
        i = new Number(id)+1;
        key = "alt";
        document.getElementById(key).innerHTML = i + "."+name;
    }
    id++;
    while (id < 1) {
        i = new Number(id)+1;
        key = "alt";
        document.getElementById("alt").innerHTML = i + ".";
        id++;
    }
}


function contains(anArray, aValue) {
    x = 0;
    while (x < anArray.length) {
        if (anArray[x] == aValue) {
            return true;
        }
        x++;
    }
    return false;
}

function remove(anArray, aValue) {
    newArray = new Array();
    for (x in anArray) {
        if (anArray[x] == aValue) {
            //alert("Match: "+anArray[x]+" - "+aValue+" = "+x);
            if (x > 0) {
                y = new Number(x)-1;
                newArray = anArray.slice(0, x);
                //alert(anArray.slice(0, x));
            }
            y =  new Number(x) + 1;
            newArray = newArray.concat(anArray.slice(y));
                //alert(anArray.slice(y));
                //alert(newArray);
            return newArray;
        }
    }
    return anArray;
}

</script>

<body <? if ($isin) { print "onload=\"loadPlayers()\""; } ?>>
<?
include "base/menu.php";
?>

<h1 align="center"><?= $title; ?></h1>
<hr size="1"/>

<?
//if ($isin) {
if (1==2) {

$sql = <<<EOD

SELECT CONCAT(p.firstname, ' ', p.lastname) as 'name', p.pos, n.nflteamid, p.playerid
FROM newplayers p
JOIN roster r ON p.playerid=r.playerid AND r.dateoff is null
LEFT JOIN nflrosters n on n.playerid=p.playerid and n.dateoff is null
WHERE r.teamid=$teamnum and p.pos<>'HC'
ORDER BY p.pos, p.lastname

EOD;

$teamResults = mysql_query($sql) or die("Unable to get team list: ".mysql_error());

print "<form action=\"processProtect.php\" method=\"post\" onsubmit=\"return checkform();\"/>";
print "<div id=\"roster\">";
print "<table><tr><th><a title=\"Protection - Limit 5\">P</a></th><th><a title=\"Pull-Back - Limit 2\">PB<a></th><th><a title=\"Alternate - Limit 1\">A</a></th><th>Player</th><th>Pos</th><th>NFL Team</th></tr>";
while ($player = mysql_fetch_assoc($teamResults)) {
    print "<tr id=\"${player["playerid"]}\" onclick=\"rotate(this)\">";
    print "<td><input type=\"checkbox\" name=\"pro[]\" value=\"${player["playerid"]}\" onclick=\"saveClick(this)\" id=\"pro${player["playerid"]}\"/></td>";
    print "<td><input type=\"checkbox\" name=\"pb[]\" value=\"${player["playerid"]}\" onclick=\"saveClick(this)\" id=\"pb${player["playerid"]}\"/></td>";
    print "<td><input type=\"checkbox\" name=\"alt[]\" value=\"${player["playerid"]}\" onclick=\"saveClick(this)\" id=\"alt${player["playerid"]}\"/></td>";
    print "<td>${player["name"]}</td><td>${player["pos"]}</td>";
    print "<td align=\"center\">${player["nflteamid"]}</td></tr>";

}
print "</table>";
print "</div>";
?>

<div id="protectBlock">

<div id="protect">
<b>Protect</b><br/>
<div id="p1">1.</div>
<div id="p2">2.</div>
<div id="p3">3.</div>
<div id="p4">4.</div>
<div id="p5">5.</div>
</div>

<div id="pullback">
<b>Pull Back</b><br/>
<div id="pb1">1.</div>
<div id="pb2">2.</div>
</div>

<div id="alternate">
<b>Alternate</b><br/>
<div id="alt">1.</div><br/>
</div>

<input type="submit" value="Submit"/>

</div>

</form>

<div style="float: left"/>

<?
} else {
?>

<!--<center><b>You must be logged in to protect players</b></center>-->
<center><b>The Expansion Draft has started you can not change protections</b></center>

<?
}

include "base/footer.html";
?>
