<?php
/**
 * @var $entityManager EntityManager
 */
require 'article/articleUtils.php';

$numArt = 4;
$article = getArticles($entityManager, $numArt);
//print_r($article);
//$article->fetch();
?>

<div class="container">
<div class="row">
    <div class="col main-art"><?= printArticleCard($article[0]) ?></div>
</div>

<div class="row mx-0">
    <div class="card-deck">
    <?php
        for ($i=1; $i<$numArt; $i++) {
            print printArticleCard($article[$i]);
            ?>
            <div class="w-100 d-none d-block d-md-none"><!-- wrap on small --></div>
        <?php
        }
    ?>
    </div>
</div>

</div>
