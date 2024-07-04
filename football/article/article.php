<?php
//require_once 'DataObjects/Articles.php';
require 'articleUtils.php';


function printComment($comment, $depth=0): void
{
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
error_log('Article: ' .print_r($article, true));

include 'view-snip.php';