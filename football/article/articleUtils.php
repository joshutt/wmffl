<?php
/**
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;
use WMFFL\orm\Article as Article;

require_once 'bootstrap.php';

/**
 * Given an id return the Article associated with it.  If no id is provided return the most recent one
 * @param $uid
 * @return Article
 */
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
}


/**
 * Return a Doctrine set of results of Articles.  The number returned is specified by $num.  Will return the most
 * recent items, unless the $start parameter is provided.  Then it will be the items starting at that point.
 * @param $num
 * @param $start
 * @return mixed
 */
function getArticles($num, $start=null ): mixed
{
    global $entityManager;

    $dql = 'SELECT a from WMFFL\orm\Article a where a.active=1 order by a.displayDate desc, a.priority desc';
    $query = $entityManager->createQuery($dql);
    $query->setMaxResults($num);

    if (!empty($start)) {
        $query->setFirstResult($start*$num);
    }

    return $query->getResult();
}


/**
 * Given an Article converts it into a printable string and returns that string
 * @param Article $article
 * @return string
 */
function printArticleCard(Article $article): string
{
    $articleId = $article->getId();
    $link = $article->getLink();
    $title = $article->getTitle();
    $date = $article->getDisplayDate()->format('M d, Y');
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
