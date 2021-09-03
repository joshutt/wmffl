<?php
require_once 'utils/start.php';
require 'articleUtils.php';

$artPerPage = 24;
$start = null;
if (array_key_exists('start', $_REQUEST) && !empty($_REQUEST['start'])) {
    $start = $_REQUEST['start'];
}
$article = getArticles($artPerPage, $start);

$title = 'Latest News';
include 'base/menu.php';
?>

    <h1 class="full"><?= $title ?></h1>

    <div class="container-fluid">
        <div class="card-deck">
            <?php
            $i = 1;
            $first = null;
            $last = null;
            while ($article->fetch()) {
                if ($i === 1) {
                    $first = $article->articleId + $artPerPage;
                }
                $last = $article->articleId;
                print printArticleCard($article);

                if ($i % 1 === 0) {
                    ?>
                    <div class="w-100 d-none d-sm-block d-md-none"><!-- wrap every 2 on sm--></div>
                    <?php
                }
                if ($i % 2 === 0) {
                    ?>
                    <div class="w-100 d-none d-md-block d-lg-none"><!-- wrap every 3 on md--></div>
                    <?php
                }
                if ($i % 3 === 0) {
                    ?>
                    <div class="w-100 d-none d-lg-block"><!-- wrap every 4 on lg--></div>
                    <?php
                }
                $i++;
            }
            ?>
        </div>

    <div class="py-2 row justify-content-between">
        <div class="float-left"><a class="btn btn-wmffl" href="list?start=<?= $last-1 ?>">&lt;&lt;&lt;
                Older</a></div>
        <div class="float-right"><a class="btn btn-wmffl" href="list?start=<?= $first ?>">Newer &gt;&gt;&gt;</a>
        </div>
    </div>

    </div>
<?php
include 'base/footer.php';
