<?php
require_once 'utils/connect.php';
#$isin = true;
#$teamnum = 2;
$dateSrc = '2023-08-23 00:05 EDT';
$dateTime = new DateTime($dateSrc);
$dateTime->sub(DateInterval::createFromDateString('6 min'));

$thequery = 'select p.playerid, p.firstname, p.lastname, p.pos, ';
$thequery .= 'p.team, ';
$thequery .= "if (pc.years is null, 0, pc.years) as 'Years', ";
$thequery .= "max(pos.cost) as 'Cost', ";
$thequery .= "if (pro.cost is null, 0, 1) as 'Protected' ";
$thequery .= 'from newplayers p ';
$thequery .= 'join roster r on p.playerid=r.playerid and r.dateoff is null ';
$thequery .= "join positioncost pos on p.pos=pos.position and pos.startSeason<=$currentSeason and pos.endSeason is null ";
$thequery .= 'left join protectioncost pc ';
$thequery .= 'on p.playerid=pc.playerid ';
$thequery .= "and pc.season=$currentSeason ";
$thequery .= 'left join protections pro ';
//$thequery .= "on pro.playerid=p.playerid and pro.teamid=r.teamid ";
$thequery .= 'on pro.playerid=p.playerid ';
$thequery .= "and pro.season=$currentSeason ";
//$thequery .= "and pro.season=pc.season ";
$thequery .= "where r.teamid=$teamnum ";
$thequery .= 'and (pos.years<=pc.years or pos.years=0) ';
$thequery .= 'GROUP BY p.playerid ';
$thequery .= 'ORDER BY p.pos, p.lastname, p.firstname';

//$ptsQuery = "select PrePtsLeft, PtsLeft from transpoints where teamid=$teamnum";
$ptsQuery = "select TotalPts, ProtectionPts from transpoints where teamid=$teamnum and season=$currentSeason";
//$ptsQuery = "select TotalPts, ProtectionPts from newtranspoints where teamid=$teamnum and season=2003";

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
            <?php
        } else {
            ?>

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

