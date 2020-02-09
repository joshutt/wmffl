<?php
require_once "utils/start.php";

$thisWeek = isset($_POST['week']) ? $_POST["week"] : '';
if ($thisWeek == "") {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason=2019;
$title = "Standings";

$clinchedList = array('Norsemen' => 'y-', 'British Bulldogs' => 't-', 'Fighting Squirrels' => 't-', 'Sean Taylor\'s Ashes' => 'e-', 'Crusaders' => 'y-', 'Testudos Revenge' => 'y-', 'Gallic Warriors' => 'e-',
                'Sacks on the Beach' => 'e-', 'Richard\'s Lionhearts' => 'x-', 'Amish Electricians' => 'e-', 'Trump Molests Collies' => 'e-', 'MeggaMen' => 'e-');

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
t - clinched Toilet Bowl berth
</p>
</center>
<?php
}

include "base/footer.html";
?>
