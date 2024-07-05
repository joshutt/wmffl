<?php
/**
 * @var $isin boolean
 */
require_once 'utils/start.php';

$title = 'Leave Comment';

$javascriptList = array('/base/vendor/js/tiny_mce_5_0/tinymce.min.js', '/base/js/comments.js');
include 'base/menu.php';
?>

<h1 class="full">Enter Comment</h1>

<?php
if (!$isin) {
    ?>
    <b>You must be logged in to submit a Trash Talk entry</b>
    <?php
} else {
    ?>

    <div class="container align-content-center">
        <form action="processEntry.php" method="post">
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" class="form-control" id="subject" name="subject"/>
            </div>
            <div class="form-group">
                <label for="body">Body:</label>
                <textarea class="form-control" id="body" name="body" rows="20"></textarea>
            </div>
            <div class="text-center">
                <input type="submit" class="btn btn-wmffl" value="Submit Entry"/>
            </div>

        </form>
    </div>

    <?php
}
?>

<?php include 'base/footer.php'; ?>
