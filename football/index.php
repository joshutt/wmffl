<?php
$title = 'The WMFFL Fantasy Football League';

$cssList = array('base/css/index.css');
$javascriptList = array('base/js/front.js');
include 'base/menu.php';

?>

<div class="card m-1">
    <?php include 'article.php' ?>
</div>
<div class="card m-1">
    <?php include 'quicklinks.php'; ?>
</div>

<!--<table width="100%" border="0">-->
<!--    <tr>-->
<!--        <td VALIGN="top" width="*">-->
<!---->
<!--            <div class="card m-1">-->
<!--                --><?php //include 'article.php' ?>
<!--            </div>-->
<!--            <div class="card m-1">-->
<!--                --><?php //include 'quicklinks.php'; ?>
<!--            </div>-->
<!---->
<!--        </td>-->
<!---->
<!--        <td align="right" valign="top" width="260">-->
<!--            <div class="card m-1">-->
<!--                --><?php //include 'scores.php' ?>
<!--            </div>-->
<!--            <div class="card m-1">-->
<!--                --><?php //include 'standings.php' ?>
<!--            </div>-->
<!--            <div class="card m-1">-->
<!--                --><?php //include 'forum/commentlist.php' ?>
<!--            </div>-->
<!--        </td>-->
<!---->
<!--    </tr>-->
<!--</table>-->

<?php include 'base/footer.php'; ?>
