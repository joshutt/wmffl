<?php
require_once 'utils/start.php';


require 'DataObjects/Forum.php';

$post = new DataObjects_Forum;
$post->orderBy('createTime DESC');
$post->limit(6);
$post->find();

?>

<div class="card-header cat text-center">LATEST TRASH TALK</div>
<div class="card-body py-0 my-1 text-left gameBox">
    <?php
while ($post->fetch()) {
    ?>
    <div class="headline p-1"><a class="NFLHeadline" href="/forum/comments#<?= $post->forumid ?>"><?= $post->gettitle() ?></a></div>
    <?php
}
?>
    <div class="headline p-1"><a href="/forum/blogentry" class="comment">Leave Commentary</a></div>
</div>



