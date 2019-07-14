<?
require_once "utils/start.php";

function numberName($num) {
    switch($num) {
        case '0' : return 'Zero';
        case '1' : return 'One';
        case '2' : return 'Two';
        case '3' : return 'Three';
        case '4' : return 'Four';
    }
}


$title = "Determine Draft Order";

include "base/menu.php";

?>
<script language="javascript">
    function checkSize(form) {
        var selection = form.word.value;
        
        if (selection.length == '0') {
            alert("Your word must be at least 1 character long");
            return false;
        } else if (selection.length > '8') {
            alert("Your word can be at most 8 characters long");
            return false;
        }
        
        return true;
    }

function inputLimiter(e,allow) {
    var AllowableCharacters = ' ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    var k;
    k=document.all?parseInt(e.keyCode): parseInt(e.which);
    if (k!=13 && k!=8 && k!=0){
        if ((e.ctrlKey==false) && (e.altKey==false)) {
            return (AllowableCharacters.indexOf(String.fromCharCode(k))!=-1);
        } else {
            return true;
        }
    } else {
        return true;
    }
} 
    
</script>


<h1 align="center">Draft Order Determination</h1>
<hr size = "1" />

<p>
In order to ensure that the draft order is fairly chosen randomly, we use a third party site called <a href="http://random.org">random.org</a> to determine the order.  We will be using their List Randomizer in advanced mode.  In part 1, the names will appear by weights in the exact order listed below.  Part 3 we will be using the third choice: a pregenerated randomization based on persistent identifier.  The identifer will be created by appending four words selected by the first four owners to respond.  The selected words must be at most 8 letters long. </p>

<p>This will allow anyone to recreate the resulting randomization by simply entering in the identifier.  At the same time it guarantees that no one team or owner (including the commissioner) can single-handedly manipulate the resulting order.</p>


<p><b>Submit a word</b></p>
<?
if ($isin) {

    $query = "SELECT `key`, value FROM config WHERE `key` like 'draft.order.%'";
    $results = mysqli_query($conn, $query) or die("Can't get config values: " . mysqli_error($conn));
    $orderArray = array();
    $inArray = 0;
    while ($row = mysqli_fetch_assoc($results)) {
        if ($row["value"] == "") {
            continue;
        }
        $spot = substr($row["key"], -6, -2);
        $num = substr($row["key"], -1);

        $keys = array_keys($orderArray);
        if (in_array($num, $keys)) {
            $set = $orderArray[$num];    
        } else {
            $set = array();
        }

        if ($spot == "word") {
            $set["word"] = $row["value"];
        } else if ($spot == "team") {
            $set["team"] = $row["value"];
            if ($teamnum == $set["team"]) {
                $inArray = $num;
            }
        }
        $orderArray[$num] = $set;
        
    }
    
    if ($inArray != 0) {
        print "<p>You submitted the word: '{$orderArray[$inArray]["word"]}' which will appear in spot $inArray of the identifier.</p>";
    }
    
    $count = count($orderArray);
    if ($count == 4) {
        $identifier = $orderArray[1]["word"] . $orderArray[2]["word"] . $orderArray[3]["word"] . $orderArray[4]["word"];
        print "<p>Four words have already been submitted.  The final identifier is '$identifier'.  The final order can be verified by going to <a href=\"http://random.org/lists/?mode=advanced\">Random.org</a> putting the identifer into part 3 (without the quotes) and the seeded order into part 1.  Press 'Randomize' to get the result.</p>";
    } else {
        print "<p>".numberName($count)." words have been submitted.  The order will be known once ".numberName(4-$count)." more are selected.</p>";
    }

    if ($count < 4 && $inArray == 0) {
?>
  <form action="wordsubmit.php" method="post" onsubmit="return checkSize(this);">
  <input type="text" size="8" maxlength="8" name="word" onkeypress="return inputLimiter(event, 'word')"/>
  <input type="submit" name="submit" value="submit" />
  <input type="hidden" name="teamid" value="<? print $teamnum; ?>"/>
  </form>
  
<?
    }
    
} else {
    print "Not Logged In";
}
    
?>

<table width="100%">
<tr><td width="*">
<p><b>Seeded Order</b></p>
<p>
Pretend I'm Not Here<br/>
Pretend I'm Not Here<br/>
Pretend I'm Not Here<br/>
Pretend I'm Not Here<br/>
Pretend I'm Not Here<br/>
Pretend I'm Not Here<br/>
Werewolves<br/>
Werewolves<br/>
Werewolves<br/>
Werewolves<br/>
Werewolves<br/>
Fighting Squirrels<br/>
Fighting Squirrels<br/>
Fighting Squirrels<br/>
Fighting Squirrels<br/>
Whiskey Tango<br/>
Whiskey Tango<br/>
Whiskey Tango<br/>
MeggaMen<br/>
MeggaMen<br/>
Gallic Warriors<br/>
</p>
</td><td width="*">
<p><b>Drawn Order</b></p>
<p>
1. Fighting Squirrels<br/>
2. MeggaMen<br/>
3. Werewolves<br/>
4. Whiskey Tango<br/>
5. MeggaMen<br/>
6. Pretend I'm Not Here<br/>
7. Pretend I'm Not Here<br/>
8. Pretend I'm Not Here<br/>
9. Gallic Warriors<br/>
10. Whiskey Tango<br/>
11. Pretend I'm Not Here<br/>
12. Whiskey Tango<br/>
13. Werewolves<br/>
14. Pretend I'm Not Here<br/>
15. Fighting Squirrels<br/>
16. Werewolves<br/>
17. Pretend I'm Not Here<br/>
18. Werewolves<br/>
19. Fighting Squirrels<br/>
20. Fighting Squirrels<br/>
21. Werewolves
</p>
</td><td width="*" valign="top">
<p><b>Final Draft Order</b></p>
<p>
1. Fighting Squirrels<br/>
2. MeggaMen<br/>
3. Werewolves<br/>
4. Pretend I'm Not Here<br/>
5. Whiskey Tango<br/>
6. Gallic Warriors<br/>
7. Norsemen<br/>
8. Lindbergh Baby Casserole<br/>
9. Crusaders<br/>
10. Sacks on the Beach<br/>
</p>
</td>

</tr>
</table>
<?
include "base/footer.html";
?>
