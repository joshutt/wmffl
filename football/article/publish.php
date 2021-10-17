<?php
$title = 'Publish Article';

$javascriptList = array('/base/vendor/js/tiny_mce_5_0/tinymce.min.js', '/base/js/article.js');
include 'base/menu.php';

if (!$isin) {
    print "You Shouldn't Be here";
    exit();
}

if (isset($errors)) {
    foreach ($errors as $name) {
        ?>
        <div class="alert alert-danger" role="alert"><?= $name ?></div>
        <?php
    }
}

if (!isset($artTitle)) {
    $artTitle = $_REQUEST['title'];
}
if (!isset($url)) {
    $url = $_REQUEST['url'];
}
if (!isset($caption)) {
    $caption = $_REQUEST['caption'];
}
if (!isset($article)) {
    $article = $_REQUEST['article'];
}

?>


    <form method="POST" action="process.php">

        <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg" for="title">Title:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="title" name="title" value="<?= $artTitle ?>"/>
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg" for="url">Image URL:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="url" name="url" value="<?= $url ?>"/>
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg" for="caption">Caption:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="caption" name="caption" value="<?= $caption ?>"/>
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg" for="article">Article:</label>
            <div class="col-sm-10">
                <textarea id="article" rows="40" name="article"><?= $article ?></textarea>
            </div>
        </div>
        <div class="text-center">
            <input class="btn btn-wmffl" type="submit" name="submit" value="Preview"/>
        </div>
    </form>

<?php
include 'base/footer.php';
