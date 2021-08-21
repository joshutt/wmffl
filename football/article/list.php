<?php
require_once 'utils/start.php';
require 'articleUtils.php';

$query = 'select articleId, title, link, displayDate, u.Name
from articles a
join user u on a.author=u.UserID ';

if (array_key_exists('start', $_REQUEST) && !empty($_REQUEST['start'])) {
    $query .= ' where articleId < '.$_REQUEST['start'];
}

$query .= ' order by displayDate desc, priority asc
limit 24';

$result = mysqli_query($conn, $query);

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
            while ($article = mysqli_fetch_array($result)) {
                if ($i === 1) {
                    $first = $article['articleId'] + 24;
                }
                $last = $article['articleId'];
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
        <div class="float-left"><a class="btn btn-wmffl" href="list?start=<?= $last ?>">&lt;&lt;&lt;
                Older</a></div>
        <div class="float-right"><a class="btn btn-wmffl" href="list?start=<?= $first ?>">Newer &gt;&gt;&gt;</a>
        </div>
    </div>

    </div>
<?php
include 'base/footer.php';
