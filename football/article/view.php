<?php
/**
 * @var $entityManager EntityManager
 */
require_once 'utils/start.php';
require_once 'articleUtils.php';

$uid = null;
if (array_key_exists('uid', $_REQUEST) && $_REQUEST['uid'] != null) {
    $uid = $_REQUEST['uid'];
}
$article = getArticle($entityManager, $uid);

include 'base/menu.php';
include 'view-snip.php';
include 'base/footer.php';
