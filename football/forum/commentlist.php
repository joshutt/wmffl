<?php
/**
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;

require_once 'utils/start.php';

$qb = $entityManager->createQueryBuilder();
$qb->select('f')
    ->from('WMFFL\orm\Forum', 'f')
    ->orderBy('f.createTime', 'DESC')
    ->setMaxResults(6);
$posts = $qb->getQuery()->getResult();
?>

<div class="card-header cat text-center">LATEST TRASH TALK</div>
<div class="card-body py-0 my-1 text-left gameBox">
    <?php
    foreach ($posts as $post) {
        ?>
        <div class="headline p-1"><a class="NFLHeadline"
                                     href="/forum/comments#<?= $post->getId() ?>"><?= $post->getTitle() ?></a></div>
        <?php
    }
    ?>
    <div class="headline p-1"><a href="/forum/blogentry" class="comment">Leave Commentary</a></div>
</div>



