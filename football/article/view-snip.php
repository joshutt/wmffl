<?php
use WMFFL\orm\Article;
/**
 * @var $article Article
 */

// Format Dates
$dateString = $article->getDisplayDate()->format('M d, Y');
?>

<div id="articleBlock" class="container">
    <h1 class="text-center titleLine1 p-1"><?= $article->getTitle() ?></h1>
    <figure class="figure col text-center p-1"><img class="figure-img img-responsive" src="/<?= $article->getLink() ?>"/>
        <div class="figure-caption caption "><?= $article->getCaption() ?></div>
    </figure>
    <div>
        <span class="newsdate">Published: <?= $dateString ?></span>
    </div>
    <div>
        <?php if (!empty($article->getAuthor())) { ?>
            <span class="byLine">By <?= $article->getAuthor()->getName() ?></span>
        <?php } ?>
    </div>
    <div class="mainStory">
        <div class="mt-2"><?= $article->getText() ?></div>
    </div>
</div>


