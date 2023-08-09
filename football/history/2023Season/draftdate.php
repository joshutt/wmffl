<?php
require_once 'utils/start.php';

$title = 'Determine Draft Date';

if (empty($draftMessage)) {

$draftMessage = <<<EOD

The default date for the draft is August 26th.  As always we check to see if a better date is available.  Please fill out the below form, letting us know when you can NOT make the draft.  You are limited to selected 4 dates you can not attend.  The date will be announced on or about August 15th.</p>

EOD;

}

include 'base/menu.php';
?>

<H1 ALIGN=Center>Draft Date Open</H1>
<HR size = "1"/>

<?php
if ($isin) {
    $thequery = "SELECT DATE_FORMAT(d.date, '%m%e'), DATE_FORMAT(d.date, '%W, %M %D'), d.attend ";
    $thequery .= "FROM draftdate d, user u WHERE d.userid=u.userid and u.username='$user' AND d.date BETWEEN '$currentSeason-07-01' AND '$currentSeason-10-01' ORDER BY d.date";
    $results = mysqli_query($conn, $thequery);

?>

    <div class="container px-2"><?= $draftMessage ?></div>

<P><FORM ACTION="../common/processdraftdate" METHOD="POST">

        <div class="container">
            <div class="row m-1">
                <div class="col-2"><h5>Can Attend?</h5></div>
                <div class="col-2"><h5>Date</h5></div>
            </div>


            <?php
while (list($date, $fulldate, $attend) = mysqli_fetch_row($results)) {
    ?>
    <div class="row m-1">
        <div class="col-2">
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input type="radio" class="form-check-input"
                           name="<?= $date ?>" <?= $attend == 'Y' ? 'CHECKED' : '' ?> value="Y"/>Yes
                </label>
            </div>
            <div class="form-check-inline">
                <label class="form-check-label">
                    <input type="radio" class="form-check-input"
                           name="<?= $date ?>" <?= $attend == 'N' ? 'CHECKED' : '' ?> value="N"/>No
                </label>
            </div>

        </div>
        <div class="col-4"><?= $fulldate ?></div>
    </div>
<?php } ?>

            <div class="row">
                <div class="col-4 mt-2 ml-3">
                    <button type="submit" class="btn-wmffl mx-auto">Submit</button>
                </div>
            </div>


        </div>
</FORM>
</P>
    <?php
} else {
?>

<CENTER><B>You must be logged in to use this feature</B></CENTER>

<?php }
include 'base/footer.php';

