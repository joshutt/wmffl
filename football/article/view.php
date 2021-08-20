<?php
require_once 'utils/start.php';
require_once 'articleUtils.php';

$article = getArticle();

// Format Dates
$dateString = date('M d, Y', strtotime($article->displayDate));


include 'base/menu.php';
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
include 'base/footer.php';
