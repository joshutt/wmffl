<?php
require_once 'DataObjects/Articles.php';

/**
 * @return DataObjects_Articles
 */
function getArticle(): DataObjects_Articles
{
    $article = new DataObjects_Articles;
    if (array_key_exists('uid', $_REQUEST) && $_REQUEST['uid'] != null) {
        $article->articleId = $_REQUEST['uid'];
    } else {
        $article->active = 1;
        $article->orderBy('displayDate desc');
        $article->orderBy('priority desc');
        $article->limit(1);
//    print_r($article);
    }
    $article->find(true);
    $article->getLinks('comments');
    $artid = $article->articleId;
    return $article;
}


function printArticleCard($article): string
{
    $articleId = $article['articleId'];
    $link = $article['link'];
    $title = $article['title'];
    $date = date('M d, Y', strtotime($article['displayDate']));
    $name = $article['Name'];

    $returnString = <<< EOT
 <div class="card mb-4 article-card">
                    <a href="/article/view?uid=$articleId">
                    <img class="card-img-top article-img" src="/$link"/>
                    <div class="card-body">
                        <h4 class="card-title">$title</h4>
                        <p class="card-text">$date<br/>$name</p>
                    </div>
                    </a>
                </div>
EOT;

    return $returnString;
}
