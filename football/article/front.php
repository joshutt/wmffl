<?php
require_once "DataObjects/Articles.php";

include "article/display.php";
?>

<table width="100%">
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
