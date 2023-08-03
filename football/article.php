<?php
require 'article/articleUtils.php';

$numArt = 4;
$article = getArticles($numArt);
$article->fetch();
?>

<div class="container">
<div class="row">
    <div class="col"><?= printArticleCard($article) ?></div>
</div>

<div class="row mx-0">
    <div class="card-deck">
    <?php
        for ($i=1; $i<$numArt; $i++) {
            $article->fetch();
            print printArticleCard($article);
            ?>
            <div class="w-100 d-none d-block d-md-none"><!-- wrap on small --></div>
        <?php
        }
    ?>
    </div>
</div>

</div>
