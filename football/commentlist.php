<?
//$source = "http://myrss.com/f/n/f/nflAdsrct1.rss";
//$source = "http://xml.newsisfree.com/feeds/52/1852.xml";
//$source = "http://p.moreover.com/cgi-local/page?c=Sports%3A%20American%20football%20news&o=rss";
$source = "$DOCUMENT_ROOT/blog/atom.xml";

#define('MAGPIE_CACHE_ON', 0);
include_once("lib/rss/magpier/rss_fetch.inc");

$rss_string = file_get_contents($source);
$output = new MagpieRSS($rss_string);
$items = $output->items;


$items = array_slice($items, 0, 6);
//$items = array_slice($items, 1, 5);

?>

<style>
  .NFLHeaderText {color:Red; text-decoration:bold; font-size:14pt}
  //.Headline  {color:Brown} 
  A.NFLHeadline:link {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:visited {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:hover {font-size:10pt; color:Red}
  .NFLNewsText  {font-size:10pt; color:Orange}
  A.NFLNewsText:link {text-decoration:none}
  A.NFLNewsText:visited {text-decoration:none}
  .NFLNewsDate {font-size:8pt}
  A.Comment:link {font-size:10pt; text-decoration:none; color:e2a500}
  A.Comment:visited {font-size:10pt; text-decoration:none; color:e2a500}
  A.Comment:hover {font-size:10pt; color:Brown}
</STYLE>

<TABLE ALIGN="left" BORDER='0' WIDTH='244' CELLPADDING='1' CELLSPACING='0'>
<TR><TD><TABLE BGCOLOR='#eeeeee' CELLPADDING='6' CELLSPACING='0'  border='0' WIDTH='244'>
<TR><TD height='24' align='center' bgcolor="#660000">
<FONT class='SectionHeader'>LATEST COMMENTARY</FONT></TD></TR>



<?
		foreach ($items as $item) {
		//print "<TR><TD><A HREF=\"".$item["link"]."\" class=\"NFLHeadline\">";
		print "<TR><TD><A HREF=\"comments.shtml\" class=\"NFLHeadline\">";
	print "<FONT class=\"NFLHeadline\">".$item["title"]."</FONT></A></TD></TR>";
//        echo "<A HREF=\"".$item["link"]."\">".$item["title"]."</A><BR>";
}

?>

<tr><td><a href="/teams/blogentry.php" class="Comment">
Leave Commentary
</a></td></tr>

</TABLE>
</table>
