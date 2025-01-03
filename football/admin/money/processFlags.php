<?php
/**
 * @var $entityManager EntityManager
 **/

use Doctrine\ORM\EntityManager;
use WMFFL\orm\SeasonFlags as Flags;

require_once 'utils/start.php';
require_once 'bootstrap.php';

$flags = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set all of the flags and zero out playoffs
    foreach ($_POST as $key => $value) {
//        print "$key => $value<br>";
        if (str_starts_with($key, 'flag')) {
            $id = str_replace('flag-', '', $key);
            $flags[$id] = array('flags'=>$value, 'div'=>0, 'po'=>0, 'fin'=>0, 'cham'=>0);
        }
    }

    // Turn on playoffs based on passed in values
    foreach ($_POST as $key => $value) {
        if (!str_starts_with($key, 'flag')) {
            $i = explode('-', $key);
            $flags[$i[1]][$i[0]] = $value ? 1 : 0;
        }
    }
}

try {
    foreach ($flags as $id => $f) {
        /* @var $currentFlags Flags */
        $currentFlags = $entityManager->find('WMFFL\orm\SeasonFlags', $id);
        if ($currentFlags->getFlags() !== $f['flags']) {
            $currentFlags->setFlags($f['flags']);
            print "Update flags to [{$f['flags']}] for {$currentFlags->getTeam()->getName()} <br/>";
        }

        if ($currentFlags->isDivisionWinner() != $f['div']) {
            $currentFlags->setDivisionWinner($f['div']);
            print "Update Div win to {$f['div']} for {$currentFlags->getTeam()->getName()} <br/>";
        }

        if ($currentFlags->isPlayoffTeam() != $f['po']) {
            $currentFlags->setPlayoffTeam($f['po']);
            print "Update Playoff to {$f['po']} for {$currentFlags->getTeam()->getName()} <br/>";
        }

        if ($currentFlags->isFinalist() != $f['fin']) {
            $currentFlags->setFinalist($f['fin']);
            print "Update Finalist to {$f['fin']} for {$currentFlags->getTeam()->getName()} <br/>";
        }

        if ($currentFlags->isChampion() != $f['cham']) {
            $currentFlags->setChampion($f['cham']);
            print "Update Champion to {$f['cham']} for {$currentFlags->getTeam()->getName()} <br/>";
        }
        $entityManager->flush();
    }
} catch (\Doctrine\ORM\OptimisticLockException|\Doctrine\ORM\Exception\ORMException $e) {
    print "Error: $e";
}
?>

<a href="../index">Menu</a>
