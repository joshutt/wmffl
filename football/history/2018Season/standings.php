<?php
require_once "utils/start.php";

$thisWeek = $_REQUEST["week"];
if ($thisWeek == "") {
    $thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason=2018;
$title = "Standings";

$clinchedList = array('Trump Molests Collies' => 'y-', 'Amish Electricians' => 'z-', 'Fighting Squirrels' => 'z-', 'Gallic Warriors' => 'e-', 'Crusaders' => 'e-', 'Norsemen' => 'y-', 'Fightin\' Bitin\' Beavers' => 'y-', 'Sean Taylor\'s Ashes' => 'e-', 'Sacks on the Beach' => 'e-', 'Testudos Revenge' => 'e-', 'MeggaMen' => 'x-', 'Richard\'s Lionhearts' => 'e-');

include "base/menu.php";
?>


<table width="100%">
<tr><td class="cat" align="center">Current Standings</td></tr></table>
<center>
<?php
include "../common/weekstandings.php";

if (!empty($clinchedList)) {
?>

<p class="my-4 text-center">
e - eliminated from playoffs<br/>
x - clinched playoff berth<br/>
y - clinched division title<br/>
z - clinched Toilet Bowl berth
</p>
</center>
<?php
}

include "base/footer.html";
?>
