<?php
// Main Articles
$article = new DataObjects_Articles;
if (array_key_exists("uid", $_REQUEST) && $_REQUEST["uid"] != null) {
	$article->articleId=$uid;
} else {
    if (array_key_exists("artSeason", $_REQUEST) && $_REQUEST["artSeason"] != null) {
        $artSeason = $_REQUEST["artSeason"]; 
        $article->whereAdd('displayDate <= \''.$artSeason.'-12-31\'');
    }
	$article->active = 1;
	$article->orderBy('displayDate desc');
	$article->orderBy('priority desc');
	$article->limit(1);
 #   print_r($article);
}
$article->find(true);
$artid = $article->articleId;
#print_r($article);


$dateString = date(" M d, Y", strtotime($article->displayDate));
if (isset($_REQUEST["artSeason"]) && $_REQUEST["artSeason"] != null) {
    $artSeason = $_REQUEST["artSeason"]; 
} else {
    $artSeason = date("Y", strtotime($article->displayDate));   
}
?>

<div class="article c1" id="article">
    <div class="row C titleLine1"><? print $article->title; ?></div>
    <div class="row C"><img src="/<? print $article->link; ?>" alt="<? print $article->caption; ?>" class="headline_photo" /></div>
    <div class="row C caption rap"><? print $article->caption; ?></div>
  <div class="mainStory">
        <span class="byline">
        <? if (!empty($article->author)) { 
            $author = $article->getLink('author');
            print "By ".$author->Name; 
        }
        ?>
        <span class="dateline">- <? print $dateString; ?></span>
        </span>

        <p><? print $article->articleText; ?></p>
    </div>
</div>
