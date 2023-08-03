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
        $years = array(2023, 2022, 2021, 2020, 2019, 2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011, 2010, 2009, 2008, 2007, 2006);

        foreach ($years as $y) {
            $st = "";
            if ($y == $artSeason) {
                $st = "selected=\"true\"";
            }
            print "<option value=\"$y\" $st>$y</option>";
        }
    ?>
    </div>
</div>

</div>