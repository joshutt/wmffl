<?php
/**
 * @var $isin boolean
 */
$title = 'Publish Article';

$javascriptList = array('/base/vendor/js/tiny_mce_5_0/tinymce.min.js', '/base/js/article.js');
include 'base/menu.php';
?>

<h1 class="full"><?=$title?></h1>

<?php
if (!$isin) {
    ?>
    <div class="text-center font-weight-bold h4">You must be logged in to use this feature</div>
    <?php
} else {

if (isset($errors)) {
    foreach ($errors as $name) {
        ?>
        <div class="alert alert-danger" role="alert"><?= $name ?></div>
        <?php
    }
}

if (!isset($artTitle)) {
    if (array_key_exists('title', $_REQUEST)) {
        $artTitle = $_REQUEST['title'];
    } else {
        $artTitle = '';
    }
}

if (!isset($url)) {
    if (array_key_exists('url', $_REQUEST)) {
        $url = $_REQUEST['url'];
    } else {
        $url = '';
    }
}

if (!isset($upload)) {
    if (array_key_exists('upload', $_REQUEST)) {
        $upload = $_REQUEST['upload'];
    } else {
        $upload = '';
    }
}

if (!isset($caption)) {
    if (array_key_exists('caption', $_REQUEST)) {
        $caption = $_REQUEST['caption'];
    } else {
        $caption = '';
    }
}

if (!isset($article)) {
    if (array_key_exists('article', $_REQUEST)) {
        $article = $_REQUEST['article'];
    } else {
        $article = '';
    }
}

?>


    <form method="POST" action="process" enctype="multipart/form-data">

    <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg font-weight-bold" for="title">Title:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="title" name="title" value="<?= $artTitle ?>"/>
            </div>
        </div>
        <div class="form-group border pb-4 pr-1">
            <label class="col-sm-2 col-form-label col-form-label-lg font-weight-bold" for="url">Image URL:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="url" name="url" value="<?= $url ?>"/>
            </div>
            <label class="col-sm-2 col-form-label col-form-label-lg font-weight-bold" for="upload">Image Upload:</label>
            <div class="col-sm-10">
                <input type="file" class="form-control pt-1 pl-1" id="upload" name="upload" value="<?= $upload ?>"/>
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg font-weight-bold" for="caption">Caption:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="caption" name="caption" value="<?= $caption ?>"/>
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 col-form-label col-form-label-lg font-weight-bold" for="article">Article:</label>
            <div class="col-sm-10">
                <div class="editableArticle border p-1" id="article[]" name="article"><?= $article ?></div>
            </div>
        </div>
        <div class="text-center">
            <input class="btn btn-wmffl" type="submit" name="submit" value="Preview"/>
        </div>
    </form>

<?php
}
include 'base/footer.php';
