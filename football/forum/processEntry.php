<?php
/**
 * @var $isin boolean
 * @var $conn mysqli
 * @var $usernum int
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use WMFFL\orm\Forum;

require_once 'utils/start.php';
require_once 'bootstrap.php';

if (!$isin) {
    header('Location: blogentry.php');
    exit;
}

try {
    $post = new Forum();
    $author = $entityManager->find('WMFFL\orm\User', $usernum);
    $post->setTitle($_POST['subject']);
    $post->setBody(str_replace('\r\n', '', $_POST['body']));
    $post->setUser($author);
    $post->setCreateTime(new DateTime());
    $entityManager->persist($post);
    $entityManager->flush();
} catch (OptimisticLockException|ORMException $e) {
}

header('Location: comments.php');
exit;
