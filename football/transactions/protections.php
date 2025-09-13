<?php
/**
 * @var $currentSeason int
 * @var $teamnum int
 * @var $isin boolean
 * @var $conn mysqli
 */
require_once 'utils/connect.php';

// Determine deadline and publish date based on true expiration
$dateSrc = '2025-08-17 00:05 EDT';
$dateTime = null;
try {
    $dateTime = new DateTime($dateSrc);
} catch (Exception $e) {
}
$dateTime->sub(DateInterval::createFromDateString('6 min'));

if (!$isin) {
    $teamnum=0;
}

// Query to get the team and costs
$thequery = <<<EOD
select p.playerid, p.firstname, p.lastname, p.pos, p.team, if (pc.years is null, 0, pc.years) as 'Years', 
    max(pos.cost) as 'Cost', if (pro.cost is null, 0, 1) as 'Protected' 
from newplayers p 
join roster r on p.playerid=r.playerid and r.dateoff is null 
join positioncost pos on p.pos=pos.position and pos.startSeason<=$currentSeason and pos.endSeason is null 
left join protectioncost pc on p.playerid=pc.playerid and pc.season=$currentSeason 
left join protections pro on pro.playerid=p.playerid and pro.season=$currentSeason 
where r.teamid=$teamnum and (pos.years<=pc.years or pos.years=0) 
GROUP BY p.playerid 
ORDER BY p.pos, p.lastname, p.firstname
EOD;


// Query for total available points
$ptsQuery = <<<EOD
select tp.TotalPts, tp.ProtectionPts, (p1.paid | p2.paid) as 'paid'
from transpoints tp
JOIN paid p1 on tp.season=p1.season and tp.teamid=p1.teamid
JOIN paid p2 on tp.season-1=p2.season and tp.teamid=p2.teamid
where tp.teamid=$teamnum and tp.season=$currentSeason
EOD;

$title = 'WMFFL Protections';
include 'base/menu.php';
?>


<H1>Protections</H1>
<HR size="1">

<?php
if ($isin) {
    $results = mysqli_query($conn, $ptsQuery) or die('Database error: ' . mysqli_error($conn));
    $pts = mysqli_fetch_row($results);
    ?>

    <SCRIPT LANGUAGE="JavaScript">

        function change(numPts, index) {
            let newVal;
            if (document["protform"]["protect[]"][index].checked) {
                newVal = eval(document["protform"].PtsUse.value) + eval(numPts);
            } else {
                newVal = eval(document["protform"].PtsUse.value) - eval(numPts);
            }
            document["protform"].PtsUse.value = newVal;
        }

        function checkForm() {
            if (eval(document["protform"].PtsUse.value) <= eval(<?php print $pts[0]; ?>))
                return true;
            alert("You protections exceed <?php print $pts[0]; ?> points");
            return false;
        }

    </SCRIPT>

    <div class="container">
        <P>
            Select the players that you wish to protect, by checking the box next to
            them and then submitting the form. You may change protections at any time
            up until the deadline: <?= $dateTime->format("h:i a T \o\\n l, F d"); ?>.</P>

        <?php
        if ($dateTime->getTimestamp() <= time()) {
            ?>
            <P><B><FONT COLOR="red">Sorry, The Deadline For Changing Protections
                        has Passed</FONT></B></P>
        <?php } else if ($pts[2] != 1) { ?>
            <div class="alert alert-secondary" role="alert">
                <p>Your account is in arrears.  You must be in good standing to submit Protections</p>
                <p><a  class="btn btn-wmffl" href="/history/teammoney">View Account Balance</a> </p>
            </div>
        <?php } else { ?>

            <form name="protform" action="saveprotections" method="POST">

                <div class="row justify-content-around">
                    <div class="col-4 my-2 py-1 align-content-center">
                        Points Allowed: <b><?= $pts[0] ?></b><br/>
                        Points Used: <input type="text" name="PtsUse" size="3" maxlength="3" onfocus="this.blur();" value="<?= $pts[1]?>"/>
                    </div>
                </div>


                <div class="row justify-content-center">
                    <div class="col-1"></div>
                    <div class="col-3"><b>Name</b></div>
                    <div class="col-1"><b>Pos</b></div>
                    <div class="col-1"><b>Cost</b></div>
                </div>

                <?php
                // Create the query
                $results = mysqli_query($conn, $thequery);
                $idx = 0;
                while (list($playerid, $firstname, $lastname, $pos, $nfl, $year, $cost, $protected) = mysqli_fetch_row($results)) {
                    $checked = ($protected == 1 || $pos === 'HC') ? "checked='true'" : '';
                    ?>
                    <div class="row justify-content-center">
                        <div class="col-1">
                            <label class="switch">
                                <input type="checkbox" name="protect[]" value="<?= $playerid ?>"
                                       onclick="change(<?= $cost ?>, <?= $idx ?>)" <?= $checked ?>/>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="col-3"><?= $firstname ?> <?= $lastname ?> (<?= $pos ?>-<?= $nfl ?>)</div>
                        <div class="col-1"><?= $pos ?></div>
                        <div class="col-1"><?= $cost ?></div>
                    </div>
                    <?php
                    $idx++;
                }
                ?>

                <div class="row justify-content-around m-2">
                    <input type="submit" name="submit" class="btn btn-wmffl" value="Submit Protections" onClick="return checkForm()"/>
                </div>
            </form>

            <?php
        }
        } else {
            ?>

            <CENTER><B>You must be logged in to submit protections</B></CENTER>

        <?php } ?>
    </div>
<?php
include 'base/footer.php';

