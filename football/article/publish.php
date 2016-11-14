<?
$title = "Publish Article";
$javascriptList = array("javascript/tiny_mce/tiny_mce.js", "publish.js");
$cssList = array("/base/css/core.css", "publish.css");
include "$DOCUMENT_ROOT/base/menu.php";

// Throw out anyone what isn't logged in
if (!$isin) {
    print "You Shouldn't Be here";
    exit();
}

if (!isset($artTitle)) {
    $artTitle = $_POST["title"];
}
if (!isset($url)) {
    $url = $_POST["url"];
}
if (!isset($caption)) {
    $caption = $_POST["caption"];
}
if (!isset($article)) {
    $article = $_POST["article"];
}

?>

<h1 align="center"><?= $title ?></h1>
<hr size="1"/>
<br/>

<?php
// Print any errors
if (isset($errors)) {
    foreach ($errors as $name) {
        print "<span class=\"errors\">$name</span><br/>";
    }
}
?>

<form method="POST" action="process.php" enctype="multipart/form-data">
<div class="pubItem"><label for="title">Title</label><input type="text" name="title" size="75" value="<?= $artTitle ?>"/></div>
<div class="pubItem"><label for="url">Image URL</label><input type="text" name="url" size="75" value="<?= $url ?>"/></div>
<div class="pubItem"><label for="image">Image Upload</label><input type="file" name="image" size="75" value="<?= $imageFile ?>"/></div>
<div class="pubItem"><label for="caption">Caption</label><input type="text" name="caption" size="75" value="<?= $caption ?>"/></div>
<div class="pubItem"><label for="article">Article</label> <textarea name="article" cols="80" rows="30"><?= $article ?></textarea></div>
<div class="pubItem"><button class="button" type="submit" name="submit" value="Preview"/>Preview</button></div>
</form>


<?
include "$DOCUMENT_ROOT/base/footer.html";
?>
