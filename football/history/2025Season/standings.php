<?php
/**
 * @var $conn mysqli
 */
require_once 'utils/start.php';

$thisWeek = $_REQUEST['week'] ?? '';
if ($thisWeek == '') {
    //$thisWeek = $currentWeek;
    $thisWeek = 17;
}
$thisSeason = 2025;
$title = 'Standings';

$clinchedList = array();
$query = "SELECT t.name, sf.flags FROM season_flags sf join teamnames t on t.season=sf.season and t.teamid=sf.teamid WHERE sf.season=$thisSeason";
$results = mysqli_query($conn, $query) or die('Error: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($results)) {
    if (!empty(trim($row['flags']))) {
        $clinchedList[$row['name']] = $row['flags'] . '-';
    }
}

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
