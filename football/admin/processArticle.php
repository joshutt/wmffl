<?
require_once "utils/start.php";

$title = $_REQUEST["title"];
$imagePath = $_REQUEST["image"];
$newName = $_REQUEST["newname"];
$caption = $_REQUEST["caption"];
$location = $_REQUEST["location"];
$body = $_REQUEST["body"];

//print "$title - $imagePath - $caption - $location - $body";


$image = imagecreatefromjpeg($imagePath);
$width = imagesx($image);
$height = imagesy($image);
$maxSize = 400;
$percent = 1.0;

if ($width >= $height && $width > $maxSize) {
    $percent = $maxSize / $width;
} elseif ($height > $maxSize) {
    $percent = $maxSize / $height;
}
$newwidth = $width * $percent;
$newheight = $height * $percent;

$thumb = imagecreatetruecolor($newwidth, $newheight);
imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

$displayName = "/images/week/2009/$newName";
$fullName = "/home/joshutt/football$displayName";
imagejpeg($thumb, $fullName);

require_once "DataObjects/Articles.php";
$article = new DataObjects_Articles;
$article->title = $title;
$article->caption = $caption;
$article->location = $location;
$article->articleText = $body;
$article->link = $displayName;
$article->active = 0;
$uid = $article->insert();


//include "http://wmffl.com/article.php?uid=$uid";

$dateString = date("d M Y", strtotime($article->displayDate));
if ($_REQUEST["artSeason"] != null) {
    $artSeason = $_REQUEST["artSeason"];
} else {
    $artSeason = date("Y", strtotime($article->displayDate));
}
?>


<form action="approveArticle.php" method="POST">
<input type="submit" value="Approve"/>
</form>

<style>
A.headline:link {color:e2a500; text-decoration:None; font-size:12pt; font-weight:bold}
A.headline:active {color:e2a500; text-decoration:None; font-size:12pt; font-weight:bold}
A.headline:visited {color:e2a500; text-decoration:None; font-size:12pt; font-weight:bold}
A.headline:hover {color:660000; text-decoration:None; font-size:12pt; font-weight:bold}

.stats {background-color:f5efef}
A.stats:link {font-size:10pt; text-decoration:none; color:660000;}
A.stats:visited {font-size:10pt; text-decoration:none; color:660000;}
A.stats:hover {font-size:10pt; text-decoration:none; color:e2a500;}

A:link {color:660000;}
A:active {color:e2a500;}
A:visited {color:660000;}
A:hover {color:e2a500;}

.row{white-space:nowrap;padding:1px 3px 1px 3px;}
.c1{background-color:#EFEFEF;}
/*.c1{background-color:#F8FCCC;}*/
.C{text-align:center;}

.titleLine1{font-size:24px;color:#660000; font-weight: bold;}
/*.titleLine1{font-size:24px;color:#004080;}*/
.titleLine2{font-size:16px;color:#CC3300;}

.headline_photo {
        margin: 0px;
        padding: 0px;
        border: 1px solid black;
}
.caption {
        font-size: 8pt;
        color: #e2a500;
        font-weight: 700;
        margin-bottom: 5px;
}

.rap{white-space:normal;}

.newsdate {
        font-size: 8pt;
        font-weight: 700;
        color: #660000;
}

.inelig {
        color: #8888BB;
        font-style: italic;
}

.mainStory {
        margin-bottom: 5px;
        font-family:Arial,Helvetica,sans-serif;
        font-size:11px;
}
.cat, .catfoot {
        background-image:url('../Local%20Settings/Temporary%20Internet%20Files/Content.IE5/QBCJWFGN/skin/default/images/specific/ce
llpic1.gif');
        background-color:#660000;
        color:#e2a500;
        height:25px;
        font-weight:700;
       white-space:nowrap;
        padding-left:8px;
        padding-right:3px;
        padding-top:0px;
        padding-bottom:0px
}

.mainTable {
    background-image: url(/images/bluestrip2.gif);
    background-repeat: repeat-y;
}

</style>


<table>
    <tr>
        <td class="row c1 rap">
            <div class="row c1 C titleLine1"><? print $article->title; ?></div>
            <div class="row c1 C"><img src="http://wmffl.com/<? print $article->link; ?>" alt="<? print $article->caption; ?>" class="headline_photo" />
            </div>
            <div class="row c1 C caption rap"><? print $article->caption; ?></div>
            <div class="mainStory">
                <p><span class="newsdate"><? print $article->location; ?></span> - <? print $article->articleText; ?></p>
            <div class="inelig"><? print $dateString; ?></div>
            </div>
        </td>
    </tr>
</table>
