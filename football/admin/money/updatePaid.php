<?php
/**
 * @var $entityManager EntityManager
 * @var int $currentSeason
 */

use Doctrine\ORM\EntityManager;
use WMFFL\orm\Paid as Paid;

require_once 'utils/start.php';
require_once 'bootstrap.php';

//$paid = new Paid();
//$entityManager->find('Paid', )
$paid = $entityManager->getRepository('WMFFL\orm\Paid')->findBy(array('season' => $currentSeason));
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="money.js"></script>
<link rel="stylesheet" type="text/css" href="/base/css/core.css"/>
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"/>

<div class="card px-0 m-2 float-left container">
    <div class="card-header font-weight-bold text-center">Paid Status</div>
    <div class="card-body">
        <table class="table table-striped table-hover table-sm text-center" id="paidTable">
            <thead>
            <tr>
                <th scope="col">Team</th>
                <th scope="col">Previous</th>
                <th scope="col">Entry Fee</th>
                <th scope="col">Late Fee</th>
                <th scope="col">Paid?</th>
            </tr>
            </thead>

            <tbody>
            <?php

            $fmt = new NumberFormatter('us_US', NumberFormatter::CURRENCY);
            /** @var Paid $p */
            foreach ($paid as $p) {
                ?>
                <tr>
                    <td><?= $p->getTeam()->getName() ?></td>
                    <td><?= $fmt->formatCurrency($p->getPrevious(), 'USD') ?></td>
                    <td><?= $fmt->formatCurrency($p->getEntry(), 'USD') ?></td>
                    <td><span id="late-<?= $p->getId() ?>" class="editable-span"><?= $fmt->formatCurrency($p->getLateFee(), 'USD') ?></span></td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" onchange="toggleChange(event);" id="paid-<?= $p->getId() ?>"
                                   value="" <?= $p->isPaid() ? 'checked="true"' : '' ?> />
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="6">
                    <button name="update" class="btn btn-wmffl" onclick="location.reload();">Update</button>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

    <a href="../index">Menu</a>