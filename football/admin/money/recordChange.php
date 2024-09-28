<?php
/**
* @var $entityManager EntityManager
 **/

use Doctrine\ORM\EntityManager;
use WMFFL\orm\Paid as Paid;

require_once 'utils/start.php';
require_once 'bootstrap.php';

// Get the values passed in
$field = $_POST['field'];
$val = $_POST['val'];
$splitField = explode('-', $field);
$param = $splitField[0];
$idx = $splitField[1];


try {/** @var Paid $paid */
    $paid = $entityManager->find('WMFFL\orm\Paid', $idx);

    switch ($param) {
        case 'paid':
            $v = filter_var($val, FILTER_VALIDATE_BOOL);
            $paid->setPaid($v);
            break;
        case 'late':

            $v =  filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $paid->setLateFee($v);
            break;
        default:
            break;
    }
    $entityManager->flush();
} catch (\Doctrine\ORM\OptimisticLockException|\Doctrine\ORM\Exception\ORMException $e) {
    print "Error: $e";
}


