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

<?php
if ($isin) {
?>
    <div class="py-2 row justify-content-between">
        <div class="float-right"><a class="btn btn-wmffl" href="publish">Write Article</a>
        </div>
    </div>
    <?php
}
    ?>

    <div class="container-fluid">
        <div class="card-deck">
            <?php
            $i = 1;
            while ($article->fetch()) {
                print printArticleCard($article);

                if ($i % 2 === 0) {
                    ?>
                    <div class="w-100 d-none d-sm-block d-md-none"><!-- wrap every 2 on sm--></div>
                    <?php
                }
                if ($i % 3 === 0) {
                    ?>
                    <div class="w-100 d-none d-md-block d-lg-none"><!-- wrap every 3 on md--></div>
                    <?php
                }
                if ($i % 4 === 0) {
                    ?>
                    <div class="w-100 d-none d-lg-block"><!-- wrap every 4 on lg--></div>
                    <?php
                }
                $i++;
            }
            ?>
        </div>

    <div class="py-2 row justify-content-between">
        <div class="float-left"><a class="btn btn-wmffl" href="list?start=<?= $start+1 ?>">&lt;&lt;&lt;
                Older</a></div>
        <?php
            if ($start > 0) {
                ?>
        <div class="float-right"><a class="btn btn-wmffl" href="list?start=<?= $start-1 ?>">Newer &gt;&gt;&gt;</a>
            <?php } ?>
        </div>
    </div>

    </div>
<?php
include 'base/footer.php';
