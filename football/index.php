<? 
$title = "The WMFFL Fantasy Football League";

$javascriptList = array("/base/js/index.js");
# $cssList = array("/base/css/index.css");
include "base/menu.php";

?>


<div class="m9 w3-display-left">
<div class="w3-card-4 w3-panel" >
<?php include "article.php"; ?>
</div>
<div class="w3-card-4 w3-panel">
<?php include "quicklinks.php"; ?>
</div>

<div class="w3-card-4">
<?php include "base/statbar.html"; ?>
</div>

</div>



<div class="m3 w3-display-right">
<div class="w3-card-4 w3-panel">
<?  include "scores.php"; ?>
</div>
<div class="w3-card-4 w3-panel">
<?	include "list.php"; ?>
</div>
<div class="w3-card-4 w3-panel">
<?	include "forum/commentlist.php"; ?>
</div>
</div>


<?
include "base/footer.html";
?>
