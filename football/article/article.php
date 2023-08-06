<?php
require_once 'DataObjects/Articles.php';
require 'articleUtils.php';


function printComment($comment, $depth=0) {
?>
<div class="w3-container w3-col l<?= 12 - $depth ?> w3-border-top w3-padding-16 w3-right">
    <div class="w3-medium" style="font-weight: bold"><?= $comment->getLink('author_id')->Name ?></div>
    <div class="w3-small" style="color: #aaa"><?= date('m/d/y h:i a', strtotime($comment->date_created)) ?></div>
    <div class="w3-container w3-padding w3-medium"><?= $comment->comment_text ?></div>
</div>
    <div class="w3-rest w3-left"></div>
<?php
    foreach ($comment->children as $childComment) {
        printComment($childComment, $depth+1);
    }
}


$article = getArticle($uid);
error_log("Articlet: ".print_r($article, true));
//print printArticleCard($article);

?>
<div id="articleBlock" class="container">
    <h1 class="text-center titleLine1 p-1"><?= $article->title ?></h1>
    <figure class="figure col text-center p-1"><img class="figure-img img-responsive" src="/<?= $article->link ?>"/>
        <div class="figure-caption caption "><?= $article->caption ?></div>
    </figure>
    <div>
        <span class="newsdate">Published: <?= $dateString ?></span>
    </div>
    <div>
        <?php if (!empty($article->author)) { ?>
            <span class="byLine">By <?= $article->getLink('author')->Name ?></span>
        <?php } ?>
    </div>
    <div class="mainStory">
        <div class="mt-2"><?= $article->articleText ?></div>
    </div>
</div>

<?php
//$comments = new DataObjects_Comments;
//$comments->article_id = $article->articleId;
//$comments->orderBy('date_created');
//$num_comments = $comments->find();
//
//
//$commentArray = array();
//$commentOut = array();
//while ($comments->fetch()) {
//    $cmmt = clone($comments);
//    $cmmt->children = array();
//    $commentArray[$comments->comment_id] = $cmmt;
//    if ($cmmt->parent_id) {
//        array_push($commentArray[$cmmt->parent_id]->children, $cmmt);
//    } else {
//        array_push($commentOut, $cmmt);
//    }
//}
//
///*
//print "<pre>";
//print_r($commentOut);
//print "</pre>";
//*/
//
//$dateFormat = 'd M Y';
//$dateFormat = 'M d, Y';
//$dateString = date($dateFormat, strtotime($article->displayDate));
//?>
<!---->
<!--<script>-->
<!--    function toogleComments() {-->
<!--        if (document.getElementById('comments').style.display == 'block') {-->
<!--            document.getElementById('comments').style.display = 'none';-->
<!--        } else {-->
<!--            document.getElementById('comments').style.display = 'block';-->
<!--        }-->
<!--    }-->
<!--</script>-->
<!---->
<!---->
<!--<div class="w3-card-2 w3-padding w3-mobile" style="background-color: #EFEFEF">-->
<!--    <div class="w3-container w3-center w3-xlarge "-->
<!--         style="color: #660000; font-weight: bold">--><?//= $article->title ?><!--</div>-->
<!---->
<!--    <div class="w3-display-container w3-padding w3-margin">-->
<!--        <div class="w3-left-align w3-display-left" style="font-style: italic">-->
<!--            By --><?//= $article->getLink('author')->Name ?><!--</div>-->
<!--        <div class="w3-right-align w3-display-right" style="font-style: italic">Published --><?//= $dateString ?><!--</div>-->
<!--    </div>-->
<!---->
<!--    <div class="w3-container w3-center w3-mobile"><img src="/--><?//= $article->link ?><!--"/></div>-->
<!---->
<!--    <div class="w3-container w3-medium">--><?//= $article->articleText ?><!--</div>-->
<!---->
<!--    <div class="w3-container w3-small" onclick="toogleComments();">-->
<!--        <p>Comments <span class="w3-badge">--><?//= $num_comments ?><!--</span>-->
<!--        <i class="fa fa-comment-o fa-lg fa-pull-left"></i></p>-->
<!--    </div>-->
<!--    <div class="w3-container" id="comments" style="display: none">-->
<!--    --><?php //// while ($comments->fetch()) {
//        foreach ($commentOut as $comment) {
//            printComment($comment);
//    } ?>
<!--    </div>-->
<!--</div>-->
<!---->
