<?php
/**
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;
use App\Entity\Article;

require_once 'bootstrap.php';

$t = new \WMFFL\Team('a', 'b',1);

//print get_include_path();
$a = new Article();
$article = $entityManager->find(Article::class, 225);

print '<pre>';
print_r($article);
print '</pre>';
