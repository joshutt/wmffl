<?php
/**
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;

require_once 'bootstrap.php';

$t = new \WMFFL\Team('a', 'b',1);

//print get_include_path();
$a = new \WMFFL\orm\Article();
$article = $entityManager->find('WMFFL\orm\Article', 225);

print '<pre>';
print_r($article);
print '</pre>';
