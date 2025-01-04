<?php
require_once 'utils/start.php';

$thisWeek = $_REQUEST['week'] ?? '';
if ($thisWeek == '') {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason = 2024;
$title = 'Standings';

$clinchedList = array( "Sigourney's Beaver" => 't-', 'Amish Electricians' => 't-', 'MeggaMen'=>'e-', 'British Bulldogs' => 'e-', 'Crusaders' => 'e-', 'Norsemen' => 'e-', 'Gallic Warriors' => 'e-', 'Trump Molests Collies' => 'y-', "Richard's Lionhearts" => 'y-', 'Testudos Revenge' => 'x-', 'Aint Nothing But a Jew Thing' => 'x-', 'Sacks on the Beach' => 'e-' );

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
