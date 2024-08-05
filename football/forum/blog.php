<?php
/**
 * @var $isin boolean
 * @var $entityManager EntityManager
 */

use Doctrine\ORM\EntityManager;
use WMFFL\orm\Forum;

require_once 'utils/setup.php';
require_once 'bootstrap.php';

// Get the 20 comments
$qb = $entityManager->createQueryBuilder();
$qb -> select('f')
    -> from('\WMFFL\orm\Forum', 'f')
    -> orderBy('f.createTime', 'DESC')
    -> setMaxResults(20);
if (array_key_exists('start', $_REQUEST)) {
    $qb->where('f.id < '.$_REQUEST['start']);
}
$posts = $qb->getQuery()->getResult();

?>


<div id="header"> 
  <div class="blog-title">Trash Talk</div>
<?php
if ($isin) {
    print "<button class='btn btn-wmffl'><a href=\"/forum/blogentry.php\">Add Comment</a></button>";
}
?>
</div>

<!-- Begin #content -->
<div id="content"> 

  <!-- Begin #main -->
  <div id="main">
   
  
<?php
$lastDay = '';
$first = null;
/* @var $post Forum */
foreach ($posts as $post) {
    $user = $post->getUser();
    $team = $user->getTeam();
    $dtObj = $post->getCreateTime();
    if (!isset($first)) {
        $first = $post->getId() + 20;
    }
    //print_r( $dtObj);

    $dtObj->setTimezone(new DateTimeZone('America/New_York'));
    $date = $dtObj -> getTimestamp();
    $day = $dtObj -> format('l, F d, Y');
    $time = $dtObj->format('g:i A');

    //$date =  strtotime($posts->createTime);
    //$day = date("l, F d, Y", $date);
    //$time = date("g:i A T", $date);
    //print_r($user);
    if ($lastDay != $day) {
        print "<div class=\"date-header mb-1 pr-2\">$day</div>";
        $lastDay = $day;
    }
    print <<<EOD
    <div class="post p-1"><a name="{$post->getId()}"></a>
        <div class="post-title">{$post->getTitle()}</div>
		  <strong>posted by {$user->getName()}, {$team->getName()} at $time</strong>
        <div class="post-body my-2"> {$post->getBody()}  </div>
        </div>

      </div>
EOD;

}

?>
<div class="py-2 row justify-content-between">
<div class="float-left"><a class="btn btn-wmffl" href="comments.php?start=<?= $post->getId() ?>">&lt;&lt;&lt; Older</a></div>
<div class="float-right"><a class="btn btn-wmffl" href="comments.php?start=<?= $first ?>">Newer &gt;&gt;&gt;</a></div>
</div>

