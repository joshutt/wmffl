<?php
/**
 * @var $currentSeason int
 * @var $conn mysqli
 */
require_once 'utils/start.php';

$lookseason = $season ?? $currentSeason;

//$query = "SELECT t.name, CONCAT(p.firstname, ' ',p.lastname), ";
//$query .= "p.pos, p.team, pro.cost ";
//$query .= "FROM newplayers p, protections pro, teamnames t ";
//$query .= "WHERE p.playerid=pro.playerid ";
//$query .= "AND pro.season=$lookseason and t.teamid=pro.teamid and t.season=pro.season ";

$query = "select t.name, t.abbrev, CONCAT(p.firstname, ' ', p.lastname),
  p.pos, r.nflteamid, pro.cost
FROM newplayers p
 LEFT JOIN nflrosters r on p.playerid=r.playerid and r.dateon <= concat($lookseason, '-08-15') and (r.dateoff is null or r.dateoff >= concat($lookseason, '-08-15'))
JOIN protections pro on p.playerid=pro.playerid
JOIN teamnames t on t.teamid=pro.teamid and t.season=pro.season
  WHERE pro.season=$lookseason ";


if (!isset($order) || $order == 'team') {
    $query .= 'ORDER BY t.name, p.pos, p.lastname ';
    $teamcheck = true;
} else {
    $query .= 'ORDER BY p.pos, p.lastname ';
    $teamcheck = false;
}

$displayArray = array();
$result = mysqli_query($conn, $query) or die('Error: ' . mysqli_error($conn));
$oldteam = null;
$oldpos = null;
$labels = array();
while (list($team, $abbv, $name, $pos, $nfl, $cost) = mysqli_fetch_row($result)) {
    if ($teamcheck) {
        $labels = array('Name' => 6, 'Pos' => 2, 'NFL' => 2, 'Cost' => 2);
        if ($oldteam != $team) {
            $teamArray = array();
            $displayArray[$team] = array();
            $oldteam = $team;
        }
        $displayArray[$team][] = array($name, $pos, $nfl, $cost);
    } else {
        $labels = array('Name' => 6, 'Team' => 2, 'NFL' => 2, 'Cost' => 2);
        if ($oldpos != $pos) {
            $teamArray = array();
            $displayArray[$pos] = array();
            $oldpos = $pos;
        }
        $displayArray[$pos][] = array($name, $abbv, $nfl, $cost);
    }
}


$title = "$lookseason Protections";
include 'base/menu.php';
?>      

<H1 ALIGN=Center><?= $lookseason ?> Protections</H1>
<HR size="1">

<ul class="nav nav-pills nav-fill col m-2 p-1 justify-content-center">
    <li class="nav-item m-2 col-2"><a class="nav-link" href="showprotections?order=team&season=<?= $lookseason ?>">By Team</a></li>
    <li class="nav-item m-2 col-2"><a class="nav-link" href="showprotections?order=pos&season=<?= $lookseason ?>">By Position</a></li>
</ul>


<div class="container-fluid">
<div class="row">
<?php
$result = mysqli_query($conn, $query) or die('Error: ' . mysqli_error($conn));

foreach ($displayArray as $key => $teamArray) {
    ?>
        <div class="col-12 col-lg-6 col-xl-4">
    <div class="card px-0 mx-0 my-2" >
        <div class="card-header text-center font-weight-bold"><?= $key ?> (<?= sizeof($teamArray) ?>)</div>
        <div class="card-body">
            <div class="container">
                <div class="row justify-content-center">
                    <?php
                    foreach ($labels as $label => $weight) {
                    ?>
                    <div class="col-<?=$weight?> px-lg-1"><strong><?= $label ?></strong></div>
                    <?php } ?>
                </div>
                <?php
                foreach ($teamArray as $team) {
                    ?>
                    <div class="row justify-content-center">
                        <?php
                        $c = 0;
                        foreach ($team as $item) {
                            ?>
                            <div class="col-<?= array_values($labels)[$c] ?> px-lg-0"><?= $item ?></div>
                        <?php
                        $c++;
                        } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
        </div>

<?php
}
?>
</div></div>
<?php
include 'base/footer.php';
?>


