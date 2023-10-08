<?php
require_once 'utils/start.php';

$sql = <<<EOD

select p.firstname, p.lastname, p.pos, ps.season, sum(ps.active) as 'active'
FROM newplayers p
JOIn playerscores ps on p.playerid=ps.playerid
WHERE ps.active is not null and p.pos='%s'
GROUP BY p.playerid, ps.season
ORDER BY sum(ps.active) desc, ps.season
LIMIT 30
EOD;

//print $sql;

// Output the players role
function printPlayer($player, $count, $hlSeason)
{
    if ($player['season'] == $hlSeason) {
        $class = 'currentSeason';
    } else {
        $class = 'oSeason';
    }

    print "<tr class='$class' scope='row'><td>$count</td><td>{$player['name']}</td>";
    print "<td>{$player['season']}</td>";
    print "<td>{$player['pts']}</td></tr>";
}

function printRankList($sql, $pos, $hlSeason = 2016, $extraList = array())
{

    global $conn;
    $result = mysqli_query($conn, sprintf($sql, $pos)) or die('Unable to run query: ' . mysqli_error($conn));

    $posArray = array();
    while ($players = mysqli_fetch_array($result)) {
        $name = $players['firstname'] . ' ' . $players['lastname'];
        $season = $players['season'];
        $pts = $players['active'];
        $player = array('name' => $name, 'season' => $season, 'pts' => $pts);
        $posArray[] = $player;
    }
    ?>

    <div class="card-body">
        <h5 class="card-title"><?= $pos ?></h5>
        <table class="card-text table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Season</th>
                <th scope="col">Pts</th>
            </tr>
            </thead>

            <tbody>
            <?php
            $count = 0;
            $limitScore = 0;
            $extraCount = 0;
            foreach ($posArray as $player) {
                $count++;
                if ($count == 10) {
                    $limitScore = $player['pts'];
                } else if ($count > 10 && $player['pts'] < $limitScore) {
                    break;
                }

                while (isset($extraList[$extraCount]) && $extraList[$extraCount]['pts'] >= $player['pts']) {
                    printPlayer($extraList[$extraCount], $count, $hlSeason);
                    $extraCount++;
                    $count++;

                    if ($count >= 10) {
                        $limitScore = $extraList[$extraCount - 1]['pts'];
                        if ($player['pts'] < $limitScore && $count != 10) {
                            break 2;
                        }
                    }
                }

                printPlayer($player, $count, $hlSeason);
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

$qbList = array(array('name' => 'Steve Young', 'season' => 1994, 'pts' => 287));
$rbList = array(array('name' => 'Emmitt Smith', 'season' => 1995, 'pts' => 262),
    array('name' => 'Terrell Davis', 'season' => 1998, 'pts' => 241),
    array('name' => 'Edgerrin James', 'season' => 2000, 'pts' => 216),
    array('name' => 'Marshall Faulk', 'season' => 2000, 'pts' => 209),
    array('name' => 'Barry Sanders', 'season' => 1997, 'pts' => 203));
$wrList = array(array('name' => 'Cris Carter', 'season' => 1995, 'pts' => 192),
    array('name' => 'Marvin Harrison', 'season' => 2001, 'pts' => 192),
    array('name' => 'Herman Moore', 'season' => 1995, 'pts' => 181),
    array('name' => 'Jerry Rice', 'season' => 1995, 'pts' => 175),
    array('name' => 'Terrell Owens', 'season' => 2001, 'pts' => 175),
    array('name' => 'Jerry Rice', 'season' => 1993, 'pts' => 173),
    array('name' => 'Marvin Harrison', 'season' => 1999, 'pts' => 173));
$teList = array(array('name' => 'Tony Gonzalez', 'season' => 2000, 'pts' => 133),
    array('name' => 'Ben Coates', 'season' => 1994, 'pts' => 112));
$kList = array(array('name' => 'Sebastian Janikowski', 'season' => 2002, 'pts' => 138));
$olList = array(array('name' => 'Pittsburgh Steelers', 'season' => 2001, 'pts' => 151),
    array('name' => 'Pittsburgh Steelers', 'season' => 1997, 'pts' => 147),
    array('name' => 'Denver Broncos', 'season' => 1998, 'pts' => 146));
$dlList = array(array('name' => 'Michael Strahan', 'season' => 2001, 'pts' => 125),
    array('name' => 'Jason Taylor', 'season' => 2002, 'pts' => 103),
    array('name' => 'John Abraham', 'season' => 2001, 'pts' => 99));
$lbList = array(array('name' => 'Ray Lewis', 'season' => 1999, 'pts' => 159),
    array('name' => 'Derrick Brooks', 'season' => 2002, 'pts' => 159),
    array('name' => 'Brian Urlacher', 'season' => 2001, 'pts' => 148),
    array('name' => 'Jeremiah Trotter', 'season' => 2001, 'pts' => 135),
    array('name' => 'Ray Lewis', 'season' => 1997, 'pts' => 131),
    array('name' => 'Junior Seau', 'season' => 1994, 'pts' => 124),
    array('name' => 'Brian Urlacher', 'season' => 2002, 'pts' => 124),
    array('name' => 'London Fletcher', 'season' => 2001, 'pts' => 123));
$dbList = array(array('name' => 'Rodney Harrison', 'season' => 1997, 'pts' => 146),
    array('name' => 'Rodney Harrison', 'season' => 2000, 'pts' => 123),
    array('name' => 'Sammy Knight', 'season' => 2002, 'pts' => 122));


$cssList = array('/base/css/history.css');
$title = 'Single Season Records';
include 'base/menu.php';
?>

<h1 class="full"><?= $title ?></h1>

<div class="row">
    <div class="card"><?php printRankList($sql, 'HC', $currentSeason); ?></div>
    <div class="card"><?php printRankList($sql, 'QB', $currentSeason, $qbList); ?></div>
    <div class="card"><?php printRankList($sql, 'RB', $currentSeason, $rbList); ?></div>
    <div class="card"><?php printRankList($sql, 'WR', $currentSeason, $wrList); ?></div>
    <div class="card"><?php printRankList($sql, 'TE', $currentSeason, $teList); ?></div>
    <div class="card"><?php printRankList($sql, 'K', $currentSeason, $kList); ?></div>
    <div class="card"><?php printRankList($sql, 'OL', $currentSeason, $olList); ?></div>
    <div class="card"><?php printRankList($sql, 'DL', $currentSeason, $dlList); ?></div>
    <div class="card"><?php printRankList($sql, 'LB', $currentSeason, $lbList); ?></div>
    <div class="card"><?php printRankList($sql, 'DB', $currentSeason, $dbList); ?></div>
</div>

<?php include 'base/footer.php'; ?>
