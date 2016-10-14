<?
$title = "Publish Article";
$javascriptList = array("javascript/tiny_mce/tiny_mce.js", "publish.js");
include "$DOCUMENT_ROOT/base/menu.php";

// Throw out anyone what isn't logged in
if (!$isin) {
    print "You Shouldn't Be here";
    exit();
}

// Print any errors
if (isset($errors)) {
    foreach ($errors as $name) {
        print "<span style=\"color: red; weight: bold\">$name</span><br/>";
    }
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


<table>
<form method="POST" action="process.php">
<tr><th>Title:</th><td><input type="text" name="title" size="75" value="<?= $artTitle ?>"/></td></tr>
<tr><th>Image URL:</th><td><input type="text" name="url" size="75" value="<?= $url ?>"/></td></tr>
<tr><th>Caption:</th><td><input type="text" name="caption" size="75" value="<?= $caption ?>"/></td></tr>
<tr><th>Article: </th><td><textarea name="article" cols="80" rows="30"><?= $article ?></textarea></td></tr>
<tr><th><input type="submit" name="submit" value="Preview"/></th></tr>
</form>
</table>


<?
include "$DOCUMENT_ROOT/base/footer.html";
?>
