<?php
/**
 * @var $isin boolean
 * @var $teamnum int
 */
require_once 'utils/start.php';
require_once 'bootstrap.php';

if(!$isin || $teamnum != 2) {
    echo "Not Authorized";
    exit();
}
