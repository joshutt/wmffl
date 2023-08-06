<?php
$title = 'The WMFFL Fantasy Football League';

$cssList = array('base/css/index.css');
$javascriptList = array('base/js/front.js');
include 'base/menu.php';

?>

<div class="row">
    <div class="col-md-8 col">

        <div class="m-2">
            <?php include 'article.php' ?>
        </div>


    </div>

    <div class="w-100 d-none d-block d-md-none"><!-- wrap every 2 on sm--></div>

    <div class="col-md-4 col">

        <div class="card text-center m-1 mb-2">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs justify-content-center" role="tablist">
                    <li role="presentation" class="btn btn-wmffl mr-1">
                        <a href="#scores" aria-controls="tab-01" role="tab" data-toggle="tab">Scores</a>
                    </li>
                    <li role="presentation" class="btn btn-wmffl ml-1">
                        <a href="#standings" aria-controls="tab-02" role="tab" data-toggle="tab">Standings</a>
                    </li>
                </ul>
            </div>
            <div class="card-body py-1 px-0">
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="scores">
                        <?php include 'scores.php' ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="standings">
                        <?php include 'standings.php' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card m-1 mb-2">
            <?php include 'forum/commentlist.php' ?>
        </div>
        <div class="card m-1">
            <?php include 'quicklinks.php'; ?>
        </div>
    </div>
</div>

</div>

<?php include 'base/footer.php'; ?>
