<?
require_once "utils/start.php";

$uid = $_REQUEST["uid"];
$edit = $_REQUEST["Edit"];
$publish = $_REQUEST["Publish"];

if (!empty($edit)) {
    // set title, url, caption, article
    $sql = "SELECT title, link, caption, articleText FROM articles where articleId=$uid";
    $result = mysql_query($sql) or die ("Dead query: ".mysql_error());
    $row = mysql_fetch_array($result);
    $artTitle = $row["title"];
    $url = "http://wmffl.com/".$row["link"];
    $caption = $row["caption"];
    $article = $row["articleText"];

    // delete old article
    $sql = "DELETE from articles WHERE articleId=$uid";
    $result = mysql_query($sql) or die ("Dead query: ".mysql_error());

    // redirect to publish, as POST
    include "publish.php";
    exit();

} else if (!empty($publish)) {
    $sql = "UPDATE articles SET active=1 where articleId=$uid";
    $result = mysql_query($sql) or die ("Dead query: ".mysql_error());
    header("Location: http://wmffl.com");
    exit();
}
?>
