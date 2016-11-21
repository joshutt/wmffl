<?php
require_once "utils/start.php";
require_once "DataObjects/Articles.php";

$title = "Article";
$cssList = array( "article.css");
include "base/menu.php";
?>

<?php
// Article LIst on right side
$articles = new  DataObjects_Articles;
$articles->active = 1;
$articles->orderBy("displayDate desc");
$articles->orderBy("priority desc");
//$articles->whereAdd("displayDate >= '$artSeason-01-01'");
//$articles->whereAdd("displayDate <= '$artSeason-12-31'");
$articles->limit(15);
$articles->find();
?>

<div id="rightPanel" class="c1">

    <div class="SectionHeader">Recent Articles</div>
<?php
while($articles->fetch()) {
    print "<div class=\"artLink\">";
    print "<a href=\"?uid={$articles->articleId}\" class=\"nflheadline\">{$articles->title}</a><br/>";
    print date('M d Y', strtotime($articles->displayDate));
    print "</div>";
}
?>

<div class="artLink"><a href="">More...</a></div>

<?php
if ($isin) {
    print "<div class=\"button\"><a href=\"/article/publish\">Add Article</a></div>";
}
?>
</div>

<? include "display.php" ?>


<?php include "base/footer.html"; ?>
