<?
require_once "$DOCUMENT_ROOT/utils/start.php";


require "DataObjects/Forum.php";

$post = new DataObjects_Forum;
$post->orderBy('createTime DESC');
$post->limit(6);
$post->find();

?>

<style>
</STYLE>


<div class="cat text-center">LATEST TRASH TALK</div>
<div class="my-1 text-left gameBox">
    <?php
while ($post->fetch()) {
    ?>
    <div class="boxScore p-1"><a class="NFLHeadline" href="/forum/comments.php"><?= $post->gettitle() ?></a></div>
    <?php
}
?>
    <div class="boxScore p-1"><a href="/forum/blogentry.php" class="comment">Leave Commentary</a></div>
</div>



