<?php
/**
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;
use WMFFL\orm\Article as Article;

//require_once 'DataObjects/Articles.php';
require_once 'bootstrap.php';

//$article = $entityManager->find('WMFFL\orm\Article', 225);
//print_r($article);


function getArticle($uid = null): Article
{
    global $entityManager;

    $qb = $entityManager->createQueryBuilder();
    $qb->select('a') -> from('WMFFL\orm\Article', 'a');
    if (!empty($uid)) {
        $qb->where('a.id = :uid')
            ->setParameter('uid', $uid);
    } else {
        $qb->where('a.active = 1')
            ->orderBy('a.displayDate desc')
            ->orderBy('a.priority desc')
            ->setMaxResults(1);
    }

    $query = $qb->getQuery();
    return $query->getSingleResult();
//
//    $article = new DataObjects_Articles;
//    if (!empty($uid)) {
//        $article->articleId = $uid;
//    } else {
//        $article->active = 1;
//        $article->orderBy('displayDate desc');
//        $article->orderBy('priority desc');
//        $article->limit(1);
////    print_r($article);
//    }
//    $article->find(true);
//    $article->getLinks('comments');
//    $artid = $article->articleId;
//    return $article;
}


function getArticles($num, $start=null )
{
    global $entityManager;

    $dql = 'SELECT a from WMFFL\orm\Article a where a.active=1 order by a.displayDate desc, a.priority desc';
    $query = $entityManager->createQuery($dql);
    $query->setMaxResults($num);

    if (!empty($start)) {
        $query->setFirstResult($start*$num);
    }

    $articles = $query->getResult();

    return $articles;


//
//    $article = new DataObjects_Articles;
//    $article->active = 1;
//    $article->orderBy('displayDate desc');
//    $article->orderBy('priority desc');
//
//    if (empty($start)) {
//        $start = 0;
//    }
//    $article->limit($start*$num, $num);
//
////    if (!empty($start)) {
////        $article->whereAdd('articleId <= '.$start);
////        $start * $num;
////    }
//
//    $article->find();
//    $article->getLinks('author');
//    return $article;
}


function printArticleCard(Article $article): string
{
    $articleId = $article->getId();
    $link = $article->getLink();
    $title = $article->getTitle();
    $date = $article->getDisplayDate()->format('M d, Y');
//    $date = date('M d, Y', strtotime($article->getDisplayDate()));
//    $name = $article->getLink('author')->Name;
    $name = $article->getAuthor()->getName();

    return <<< EOT
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
}
