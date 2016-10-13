<?
require_once "$DOCUMENT_ROOT/utils/setup.php";

require "DataObjects/Forum.php";

$posts = new DataObjects_Forum;
$posts->orderBy('createTime DESC');
$posts->limit(20);
$posts->find();
?>


<style type="text/css">
<!--
.blog-title {
	font-family: Georgia, "Times New Roman", Times, serif;
	font-size: 24px;
	font-weight: bolder;
	color: #000000;
	padding-bottom: 24px;
	text-align: center;
}
.date-header {
	font-family: Georgia, "Times New Roman", Times, serif;
	font-size: 16px;
	font-weight: bolder;
	color: #6A0000;
	text-align: right;
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #6A0000;
}
.post-title {
	font-family: Georgia, "Times New Roman", Times, serif;
	font-size: 16px;
	font-weight: bolder;
	color: #6A0000;
}
body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
	color: #000000;
}
.post-body {
	margin-bottom: 12px;
	border-bottom-width: 1px;
	border-bottom-style: dashed;
	border-bottom-color: #990000;
	padding-right: 36px;
	padding-left: 36px;
}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="MSSmartTagsPreventParsing" content="true" />
<meta name="generator" content="Blogger" />
<link rel="alternate" type="application/atom+xml" title="WMFFL Owners Blog - Atom" href="atom.xml" />
<link rel="alternate" type="application/rss+xml" title="WMFFL Owners Blog - RSS" href="rss.xml" />
<link rel="service.post" type="application/atom+xml" title="WMFFL Owners Blog - Atom" href="http://www.blogger.com/feeds/8508375/posts/default" />
<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://www.blogger.com/rsd.g?blogID=8508375" />
 <link rel="stylesheet" type="text/css" href="http://www.blogger.com/css/blog_controls.css"/> <link rel="stylesheet" type="text/css" href="http://www.blogger.com/dyn-css/authorization.css?targetBlogID=8508375&zx=5b27dbea-c74d-4243-9a7e-ca6c275900e7"/> 

<div id="header"> 
  <div class="blog-title">Trash Talk</div>
<?
if ($isin) {
    print "<a href=\"/forum/blogentry.php\">Add Comment</a>";
}
?>
</div>

<!-- Begin #content -->
<div id="content"> 

  <!-- Begin #main -->
  <div id="main">
   
  
<?
$lastDay = "";
while($posts->fetch()) {
    $user = $posts->getLink('userid');
    $team = $user->getLink('TeamID');
    $date =  strtotime($posts->createTime);
    $day = date("l, F d, Y", $date);
    $time = date("g:i A", $date);
    //print_r($user);
    if ($lastDay != $day) {
        print "<div class=\"date-header\">$day</div>";
        $lastDay = $day;
    }
    print <<<EOD
    <div class="post"><a name="{$posts->forumid}"></a>
        <div class="post-title">{$posts->title}</div>
		  <strong>posted by {$user->Name}, {$team->Name} at $time</strong>
        <div class="post-body"> <div style="clear:both;"></div>{$posts->body} <div style="clear:both; padding-bottom:0.25em"></div> </div>
        </div>

       <p class="post-footer">  
            
        </p>
      </div>
EOD;

}

?>
