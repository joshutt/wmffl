<?php
require_once 'utils/start.php';

$thisWeek = isset($_REQUEST['week']) ? $_REQUEST['week'] : '';
if ($thisWeek == '') {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason = 2021;
$title = 'Standings';

$clinchedList = array('Gallic Warriors' => 't-', 'Amish Electricians' => '', 'British Bulldogs' => '', 'Richard\'s Lionhearts' => '', 'Trump Molests Collies' => 'y-', 'Testudos Revenge' => '', 'Sean Taylor\'s Ashes' => 'x-', 'Norsemen' => 'y-', 'Crusaders' => '', 'Fighting Squirrels' => '', 'MeggaMen' => 'y-', 'Sacks on the Beach' => 't-');

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
