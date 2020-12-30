<?php
require_once "utils/start.php";

$thisWeek = isset($_REQUEST['week']) ? $_REQUEST["week"] : '';
if ($thisWeek == "") {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason = 2020;
$title = "Standings";

$clinchedList = array('Gallic Warriors' => 't-', 'Amish Electricians' => 'y-', 'British Bulldogs' => 't-', 'Richard\'s Lionhearts' => 'e-', 'Trump Molests Collies' => 'e-', 'Testudos Revenge' => 'e-', 'Sean Taylor\'s Ashes' => 'x-', 'Norsemen' => 'e-', 'Crusaders' => 'y-', 'Fighting Squirrels' => 'e-', 'MeggaMen' => 'y-', 'Sacks on the Beach' => 'e-');

include "base/menu.php";
?>


<table width="100%">
    <tr>
        <td class="cat" align="center">Current Standings</td>
    </tr>
</table>
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
