<?
require_once "utils/start.php";

$uid = $_REQUEST["uid"];

require_once "DataObjects/Articles.php";
$article = new DataObjects_Articles;
$article->articleId = $uid;
$article->find(true);

print "Title: ".$article->title;

$article->active = 1;
$article->update($article);

print "Approved";
?>
