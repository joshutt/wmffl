<?php
require_once "utils/setup.php";

require "DataObjects/Forum.php";

$posts = new DataObjects_Forum;
$posts->orderBy('createTime DESC');
$posts->limit(20);
if ($_REQUEST["start"]) {
    $posts->whereAdd('forumid < '.$start);
}

$posts->find();
?>


<div id="header"> 
  <div class="blog-title">Trash Talk</div>
<?php
if ($isin) {
    print "<a href=\"/forum/blogentry.php\">Add Comment</a>";
}
?>
</div>

<!-- Begin #content -->
<div id="content"> 

  <!-- Begin #main -->
  <div id="main">
   
  
<?php
$lastDay = "";
$first = null;
while($posts->fetch()) {
    $user = $posts->getLink('userid');
    $team = $user->getLink('TeamID');
    $dtObj = new DateTime($posts->createTime);
    if (!isset($first)) {
        $first = $posts->forumid + 20;
    }
    //print_r( $dtObj);

    $dtObj->setTimezone(new DateTimeZone('America/New_York'));
    $date = $dtObj -> getTimestamp();
    $day = $dtObj -> format("l, F d, Y");
    $time = $dtObj->format("g:i A");

    //$date =  strtotime($posts->createTime);
    //$day = date("l, F d, Y", $date);
    //$time = date("g:i A T", $date);
    //print_r($user);
    if ($lastDay != $day) {
        print "<div class=\"date-header mb-1 pr-2\">$day</div>";
        $lastDay = $day;
    }
    print <<<EOD
    <div class="post p-1"><a name="{$posts->forumid}"></a>
        <div class="post-title">{$posts->title}</div>
		  <strong>posted by {$user->Name}, {$team->Name} at $time</strong>
        <div class="post-body my-2"> {$posts->body}  </div>
        </div>

      </div>
EOD;

}

?>
<div class="py-2">
<div class="float-left"><a href="comments.php?start=<?= $first ?>">&lt;&lt;&lt; Newer</a></div>
<div class="float-right"><a href="comments.php?start=<?= $posts->forumid ?>">Older &gt;&gt;&gt;</a></div>
</div>

