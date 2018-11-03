<?php
require_once "DataObjects/Articles.php";

// Get the infomation for the main article
$article = new DataObjects_Articles;
// If given an Id use that one, otherwise most current
if (array_key_exists("uid", $_REQUEST) && $_REQUEST["uid"] != null) {
    $article->articleId = $uid;
} else {
    if (array_key_exists("artSeason", $_REQUEST) && $_REQUEST["artSeason"] != null) {
        $artSeason = $_REQUEST["artSeason"];
        $article->whereAdd('displayDate <= \'' . $artSeason . '-12-31\'');
    }
    $article->active = 1;
    $article->orderBy('displayDate desc');
    $article->orderBy('priority desc');
    $article->limit(1);
}
$article->find(true);
$artid = $article->articleId;

// Format Dates
$dateString = date("M d, Y", strtotime($article->displayDate));
//$dateString = date("d M Y", strtotime($article->displayDate));
if (isset($_REQUEST["artSeason"]) && $_REQUEST["artSeason"] != null) {
    $artSeason = $_REQUEST["artSeason"];
} else {
    $artSeason = date("Y", strtotime($article->displayDate));
}

// Get the Preview articles
$previewArts = new  DataObjects_Articles;
$previewArts->active = 1;
$previewArts->orderBy("displayDate desc");
$previewArts->orderBy("priority desc");
$previewArts->whereAdd("displayDate >= '$artSeason-01-01'");
$previewArts->whereAdd("displayDate <= '$artSeason-12-31'");
$previewArts->find();

?>

<div id="articleBlock" class="container c1">
    <div class="col text-center titleLine1 p-1"><?= $article->title ?></div>
    <figure class="figure col text-center p-1"><img class="figure-img img-responsive" src="<?= $article->link ?>"/>
        <div class="figure-caption caption "><?= $article->caption ?></div>
    </figure>
    <div class="mainStory">
        <?php if (!empty($article->author)) { ?>
            <span class="">By <?= $article->getLink('author')->Name ?></span> -
        <?php } ?>
        <span><?= $dateString ?></span>
        <div class="mt-2"><?= $article->articleText ?></div>
    </div>
</div>

<div class="SectionHeader cat">Past Headlines</div>
<div class="c1 text-center ">
    <select id="news" class="m-1" onchange="changenews()">
        <?php
        while ($previewArts->fetch()) {
            $dateString = date("d M Y", strtotime($previewArts->displayDate));
            if ($previewArts->articleId == $artid) {
                $selectString = " selected=\"selected\" ";
            } else {
                $selectString = " ";
            }
            print "<option value=\"{$previewArts->articleId}\" $selectString>$dateString - {$previewArts->title}</option>";
        }
        ?>
    </select>
    <select id="artSeason" class="m-1" onchange="changeyear()">
        <?php
        $years = array(2018, 2017, 2016, 2015, 2014, 2013, 2012, 2011, 2010, 2009, 2008, 2007, 2006);

        foreach ($years as $y) {
            $st = "";
            if ($y == $artSeason) {
                $st = "selected=\"true\"";
            }
            print "<option value=\"$y\" $st>$y</option>";
        }

        ?>
    </select>
</div>


