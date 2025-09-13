<?php
/**
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;
use WMFFL\orm\Article as Article;

require_once 'bootstrap.php';

/**
 * Given an id return the Article associated with it.  If no id is provided return the most recent one
 * @param EntityManager $entityManager
 * @param null $uid
 * @return Article|null
 */
function getArticle(EntityManager $entityManager, $uid = null): ?Article
{
    if (!empty($uid)) {
        return $entityManager->getRepository(Article::class)->find($uid);
    } else {
        $dql = 'SELECT a FROM WMFFL\orm\Article a WHERE a.active = 1 ORDER BY a.displayDate DESC, a.priority DESC';
        $query = $entityManager->createQuery($dql);
        $query->setMaxResults(1);
        return $query->getSingleResult();
    }
}


/**
 * Return a Doctrine set of results of Articles.  The number returned is specified by $num.  Will return the most
 * recent items, unless the $start parameter is provided.  Then it will be the items starting at that point.
 * @param EntityManager $entityManager
 * @param $num
 * @param int|null $start
 * @return Article[]
 */
function getArticles(EntityManager $entityManager, $num, $start=null ): array
{
//    $dql = 'SELECT a from WMFFL\orm\Article a where a.active=1 order by a.displayDate desc, a.priority desc';
//    $dql = 'SELECT a from WMFFL\orm\Article a where a.active=1 order by a.displayDate desc, a.priority desc';
//    $dql = 'SELECT a FROM WMFFL\orm\Article a JOIN FETCH a.author u WHERE a.active = 1 ORDER BY a.displayDate DESC, a.priority DESC';
    $dql = 'SELECT a, u from WMFFL\orm\Article a JOIN a.author u where a.active=1 order by a.displayDate desc, a.priority asc';
//    $dql = 'SELECT a.author from WMFFL\orm\Article a where a.active=1 order by a.displayDate desc, a.priority desc';
    $query = $entityManager->createQuery($dql);
    $query->setMaxResults($num);

    if (!empty($start)) {
        $query->setFirstResult($start*$num);
    }
////    dump($query->getSQL()); die();
////    $results = $query->getArrayResult();
////    dump($results); die();
//    $result = $query->getScalarResult();
//    dump($result); die();
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
    // Ensure author is not null before calling getName, if author can be optional
    $authorName = '';
    if ($article->getAuthor()) {
        $authorName = $article->getAuthor()->getName();
    }
    return <<< EOT
 <div class="card mb-4 article-card">
                    <a href="/article/view?uid=$articleId">
                    <img class="card-img-top article-img" src="/$link"/>
                    <div class="card-body">
                        <h4 class="card-title">$title</h4>
                        <p class="card-text">$date<br/>$authorName</p>
                    </div>
                    </a>
                </div>
EOT;
}
