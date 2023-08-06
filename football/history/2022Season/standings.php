<?php
require_once 'utils/start.php';

$thisWeek = $_REQUEST['week'] ?? '';
if ($thisWeek == '') {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason = 2022;
$title = 'Standings';

$clinchedList = array('Gallic Warriors' => 'e-', 'Amish Electricians' => 'e-', 'British Bulldogs' => 'e-', 'Richard\'s Lionhearts' => 't-', 'Trump Molests Collies' => 'y-', 'Testudos Revenge' => 'x-', 'Sean Taylor\'s Ashes' => 'e-', 'Norsemen' => 'y-', 'Crusaders' => 't-', 'Fighting Squirrels' => 'e-', 'MeggaMen' => 'y-', 'Sacks on the Beach' => 'e-');

include 'base/menu.php';
?>


<table width="100%">
    <tr>
        <td class="cat" align="center">Current Standings</td>
    </tr>
</table>
<center>
    <?php
    include 'history/common/weekstandings.php';

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

include 'base/footer.php';
?>
