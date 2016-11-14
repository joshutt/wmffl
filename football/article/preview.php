<?php
$cssList = array("/base/css/core.css", "publish.css");
include "base/menu.php";
?>

<? include "article.php"; ?>

<form action="confirm.php" method="post">
<input type="hidden" name="uid" value="<?= $uid ?>" />
<div class="pubItem"><button class="button" type="submit" name="Edit" value="Edit">Edit</button>
<button class="button" type="submit" name="Publish" value="Publish">Publish</button></div>
</form>

<?
include "base/footer.html";
?>
