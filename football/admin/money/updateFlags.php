<?php
/**
 * @var $entityManager EntityManager
 * @var int $currentSeason
 */

use Doctrine\ORM\EntityManager;

require_once 'utils/start.php';
require_once 'bootstrap.php';

$flags = $entityManager->getRepository('WMFFL\orm\SeasonFlags')->findBy(array('season' => $currentSeason));
?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="/base/css/core.css"/>
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"/>

<div class="card px-0 m-2 float-left container">
    <div class="card-header font-weight-bold text-center">Season Flags</div>
    <div class="card-body">
        <form action="processFlags" method="post">
            <table class="table table-striped table-hover table-sm text-center" id="paidTable">
                <thead>
                <tr>
                    <th scope="col">Team</th>
                    <th scope="col">Flags</th>
                    <th scope="col">Division</th>
                    <th scope="col">Playoffs</th>
                    <th scope="col">Finalist</th>
                    <th scope="col">Champion</th>
                </tr>
                </thead>

                <tbody>
                <?php
                foreach ($flags as $f) {
                    $id = $f->getId();
                    ?>
                    <tr>
                        <td><?= $f->getTeam()->getName() ?></td>
                        <td><input type="text" name="flag-<?= $id ?>" value="<?= $f->getFlags() ?>" size="3"/></td>
                        <td><input type="checkbox" name="div-<?= $id ?>" <?= $f->isDivisionWinner() ? 'checked' : '' ?> /></td>
                        <td><input type="checkbox" name="po-<?= $id ?>" <?= $f->isPlayoffTeam() ? 'checked' : '' ?> /></td>
                        <td><input type="checkbox" name="fin-<?= $id ?>"<?= $f->isFinalist() ? 'checked' : '' ?> /></td>
                        <td><input type="checkbox" name="cham-<?= $id ?>" <?= $f->isChampion() ? 'checked="true"' : '' ?> /></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <th colspan="6"><input type="submit"/></th>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
    <a href="../index">Menu</a>
</div>

