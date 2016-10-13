<?
require_once "DataObjects/Articles.php";

$article = new DataObjects_Articles;
if (array_key_exists("uid", $_REQUEST) && $_REQUEST["uid"] != null) {
	$article->articleId=$uid;
} else {
    if (array_key_exists("artSeason", $_REQUEST) && $_REQUEST["artSeason"] != null) {
        $artSeason = $_REQUEST["artSeason"]; 
        $article->whereAdd('displayDate <= \''.$artSeason.'-12-31\'');
    }
	$article->active = 1;
	$article->orderBy('displayDate desc');
	$article->orderBy('priority desc');
	$article->limit(1);
//    print_r($article);
}
$article->find(true);
$artid = $article->articleId;
#print_r($article);


$dateString = date("d M Y", strtotime($article->displayDate));
if (isset($_REQUEST["artSeason"]) && $_REQUEST["artSeason"] != null) {
    $artSeason = $_REQUEST["artSeason"]; 
} else {
    $artSeason = date("Y", strtotime($article->displayDate));   
}
?>
<table width="100%">
	<tr>
		<td class="row c1 rap">
			<div class="row c1 C titleLine1"><? print $article->title; ?></div>
			<div class="row c1 C"><img src="/<? print $article->link; ?>" alt="<? print $article->caption; ?>" class="headline_photo" />
			</div>
			<div class="row c1 C caption rap"><? print $article->caption; ?></div>
			<div class="mainStory">
				<p>
                <? if (!empty($article->author)) { 
                    $author = $article->getLink('author');
                    print "By ".$author->Name."</p>"; 
                }
                ?>

				<p><? print $article->articleText; ?></p>
			<div class="inelig"><? print $dateString; ?></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="cat">Past Headlines</td>
	</tr>
<?
$articles = new  DataObjects_Articles;
$articles->active = 1;
$articles->orderBy("displayDate desc");
$articles->orderBy("priority desc");
$articles->whereAdd("displayDate >= '$artSeason-01-01'");
$articles->whereAdd("displayDate <= '$artSeason-12-31'");
$articles->find();
?>
	<tr>
		<td class="row c1 C">
			<select id="news" style="margin:5px" onchange="changenews()">
				<?
					while($articles->fetch()) {
						$dateString = date("d M Y", strtotime($articles->displayDate));
						if ($articles->articleId == $artid) {
							$selectString = " selected=\"selected\" ";
						} else {
							$selectString = " ";
						}
						print "<option value=\"{$articles->articleId}\" $selectString>$dateString - {$articles->title}</option>";
					}
				?>
			</select>
            <select id="artSeason" style="margin:5px" onchange="changeyear()">
<?
$years = array(2016, 2015, 2014, 2013, 2012, 2011, 2010, 2009, 2008, 2007, 2006);

foreach($years as $y) {
    $st = "";
    if ($y == $artSeason) {
        $st = "selected=\"true\"";
    }
    print "<option value=\"$y\" $st>$y</option>";
}

?>
            </select>
		</td>
	</tr>
</table>
